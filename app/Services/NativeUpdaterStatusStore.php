<?php

namespace App\Services;

use App\Events\NativeUpdaterStatusChanged;
use Illuminate\Support\Facades\Cache;

class NativeUpdaterStatusStore
{
    private const CACHE_KEY = 'nativephp.auto_updater.history';
    private const STATE_KEY = 'nativephp.auto_updater.state';
    private const HISTORY_LIMIT = 10;
    private const TTL_HOURS = 6;

    public function push(string $event, string $message, array $meta = []): array
    {
        $entry = [
            'event' => $event,
            'message' => $message,
            'meta' => $meta,
            'timestamp' => now()->toIso8601String(),
        ];

        $history = Cache::get(self::CACHE_KEY, []);

        if (!empty($history) && $this->isDuplicateEntry($history[0], $entry)) {
            $history[0]['timestamp'] = $entry['timestamp'];
        } else {
            array_unshift($history, $entry);
            $history = array_slice($history, 0, self::HISTORY_LIMIT);
        }

        Cache::put(self::CACHE_KEY, $history, now()->addHours(self::TTL_HOURS));
        $this->syncState($event, $entry);
        $this->broadcastUpdate($history);

        return $history;
    }

    public function latest(?int $limit = null): array
    {
        $history = Cache::get(self::CACHE_KEY, []);

        if ($limit !== null) {
            return array_slice($history, 0, $limit);
        }

        return $history;
    }

    public function state(): array
    {
        return Cache::get(self::STATE_KEY, $this->defaultState());
    }

    public function ensureFresh(string $currentVersion): void
    {
        $state = $this->state();
        if (($state['installedVersion'] ?? null) !== $currentVersion) {
            $this->resetState($currentVersion);
            Cache::forget(self::CACHE_KEY);
        }
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::STATE_KEY);
    }

    private function syncState(string $event, array $entry): void
    {
        $state = $this->state();
        $state['lastEvent'] = $event;
        $state['lastMessage'] = $entry['message'];
        $state['lastUpdatedAt'] = $entry['timestamp'];
        $state['installedVersion'] = config('nativephp.version');

        switch ($event) {
            case 'checking':
                $state = array_merge($state, [
                    'stage' => 'checking',
                    'availableVersion' => null,
                    'availableReleaseName' => null,
                    'downloadPercent' => 0,
                    'downloadTransferred' => 0,
                    'downloadTotal' => 0,
                    'canDownload' => false,
                    'canInstall' => false,
                ]);
                break;
            case 'available':
                $state = array_merge($state, [
                    'stage' => 'available',
                    'availableVersion' => $entry['meta']['version'] ?? null,
                    'availableReleaseName' => $entry['meta']['releaseName'] ?? null,
                    'downloadPercent' => 0,
                    'downloadTransferred' => 0,
                    'downloadTotal' => 0,
                    'canDownload' => true,
                    'canInstall' => false,
                ]);
                break;
            case 'download_requested':
                $state = array_merge($state, [
                    'stage' => 'download_requested',
                    'availableVersion' => $state['availableVersion'] ?? ($entry['meta']['version'] ?? null),
                    'availableReleaseName' => $state['availableReleaseName'] ?? ($entry['meta']['releaseName'] ?? null),
                    'downloadPercent' => 0,
                    'downloadTransferred' => 0,
                    'downloadTotal' => 0,
                    'canDownload' => false,
                    'canInstall' => false,
                ]);
                break;
            case 'downloading':
                $state = array_merge($state, [
                    'stage' => 'downloading',
                    'availableVersion' => $state['availableVersion'] ?? ($entry['meta']['version'] ?? null),
                    'downloadPercent' => $entry['meta']['percent'] ?? 0,
                    'downloadTransferred' => $entry['meta']['transferred'] ?? 0,
                    'downloadTotal' => $entry['meta']['total'] ?? 0,
                    'canDownload' => false,
                    'canInstall' => false,
                ]);
                break;
            case 'downloaded':
                $state = array_merge($state, [
                    'stage' => 'downloaded',
                    'availableVersion' => $entry['meta']['version'] ?? ($state['availableVersion'] ?? null),
                    'availableReleaseName' => $entry['meta']['releaseName'] ?? ($state['availableReleaseName'] ?? null),
                    'downloadPercent' => 100,
                    'canDownload' => false,
                    'canInstall' => true,
                ]);
                break;
            case 'cancelled':
                $state = array_merge($state, [
                    'stage' => 'cancelled',
                    'availableVersion' => $state['availableVersion'] ?? null,
                    'canDownload' => true,
                    'canInstall' => false,
                ]);
                break;
            case 'not_available':
                $this->resetState();
                return;
            case 'error':
                $state = array_merge($state, [
                    'stage' => 'error',
                    'availableVersion' => $state['availableVersion'] ?? null,
                    'canDownload' => false,
                    'canInstall' => false,
                ]);
                break;
        }

        Cache::put(self::STATE_KEY, $state, now()->addHours(self::TTL_HOURS));
    }

    private function broadcastUpdate(array $history): void
    {
        event(new NativeUpdaterStatusChanged(
            state: $this->state(),
            history: $history,
        ));
    }

    private function resetState(?string $currentVersion = null): void
    {
        $state = $this->defaultState($currentVersion);
        Cache::put(self::STATE_KEY, $state, now()->addHours(self::TTL_HOURS));
    }

    private function defaultState(?string $currentVersion = null): array
    {
        return [
            'stage' => 'idle',
            'availableVersion' => null,
            'availableReleaseName' => null,
            'downloadPercent' => 0,
            'downloadTransferred' => 0,
            'downloadTotal' => 0,
            'canDownload' => false,
            'canInstall' => false,
            'lastEvent' => null,
            'lastMessage' => null,
            'lastUpdatedAt' => null,
            'installedVersion' => $currentVersion ?? config('nativephp.version'),
        ];
    }

    /**
     * Check if there is an available update.
     */
    public function hasAvailableUpdate(): bool
    {
        $state = $this->state();
        return $state['stage'] === 'available' && !empty($state['availableVersion']);
    }

    /**
     * Get the available version if any.
     */
    public function getAvailableVersion(): ?string
    {
        return $this->state()['availableVersion'] ?? null;
    }

    private function isDuplicateEntry(array $existing, array $incoming): bool
    {
        return $existing['event'] === $incoming['event']
            && $existing['message'] === $incoming['message']
            && $existing['meta'] === $incoming['meta'];
    }
}
