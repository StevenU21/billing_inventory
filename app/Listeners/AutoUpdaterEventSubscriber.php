<?php

namespace App\Listeners;

use App\Services\NativeUpdaterStatusStore;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Native\Desktop\Events\AutoUpdater\CheckingForUpdate;
use Native\Desktop\Events\AutoUpdater\DownloadProgress;
use Native\Desktop\Events\AutoUpdater\Error;
use Native\Desktop\Events\AutoUpdater\UpdateAvailable;
use Native\Desktop\Events\AutoUpdater\UpdateCancelled;
use Native\Desktop\Events\AutoUpdater\UpdateDownloaded;
use Native\Desktop\Events\AutoUpdater\UpdateNotAvailable;

class AutoUpdaterEventSubscriber
{
    public function __construct(private readonly NativeUpdaterStatusStore $statusStore) {}

    public function handleChecking(CheckingForUpdate $event): void
    {
        $this->statusStore->push('checking', 'Buscando actualizaciones...');
    }

    public function handleUpdateAvailable(UpdateAvailable $event): void
    {
        $this->statusStore->push('available', 'Nueva versión disponible.', [
            'version' => $event->version,
            'releaseName' => $event->releaseName,
            'releaseDate' => $event->releaseDate,
        ]);
    }

    public function handleUpdateNotAvailable(UpdateNotAvailable $event): void
    {
        $this->statusStore->push('not_available', 'No hay actualizaciones disponibles.', [
            'version' => $event->version,
            'releaseDate' => $event->releaseDate,
        ]);
    }

    public function handleUpdateCancelled(UpdateCancelled $event): void
    {
        $this->statusStore->push('cancelled', 'La descarga fue cancelada.', [
            'version' => $event->version,
        ]);
    }

    public function handleProgress(DownloadProgress $event): void
    {
        $this->statusStore->push('downloading', 'Descargando actualización...', [
            'percent' => round($event->percent, 2),
            'transferred' => $event->transferred,
            'total' => $event->total,
        ]);
    }

    public function handleDownloaded(UpdateDownloaded $event): void
    {
        $this->statusStore->push('downloaded', 'Actualización descargada y lista para instalar.', [
            'version' => $event->version,
            'releaseName' => $event->releaseName,
        ]);
    }

    public function handleError(Error $event): void
    {
        $payload = $this->formatErrorPayload($event);

        $this->statusStore->push('error', $payload['message'], $payload['meta']);
    }

    private function formatErrorPayload(Error $event): array
    {
        $detail = $event->message ?? '';

        if ($this->looksLikeMissingRelease($detail)) {
            return [
                'message' => 'No se encontró una actualización publicada.',
                'meta' => [
                    'detail' => 'Publica un release estable en GitHub antes de buscar actualizaciones.',
                    'provider' => 'github',
                ],
            ];
        }

        return [
            'message' => 'Error durante la verificación de actualizaciones.',
            'meta' => [
                'name' => $event->name,
                'detail' => Str::limit($detail, 400),
                'stack' => Str::limit($event->stack ?? '', 400),
            ],
        ];
    }

    private function looksLikeMissingRelease(string $detail): bool
    {
        if ($detail === '') {
            return false;
        }

        return Str::contains($detail, 'Unable to find latest version on GitHub')
            || (Str::contains($detail, '404') && Str::contains($detail, 'releases/latest'));
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(CheckingForUpdate::class, [self::class, 'handleChecking']);
        $events->listen(UpdateAvailable::class, [self::class, 'handleUpdateAvailable']);
        $events->listen(UpdateNotAvailable::class, [self::class, 'handleUpdateNotAvailable']);
        $events->listen(UpdateCancelled::class, [self::class, 'handleUpdateCancelled']);
        $events->listen(DownloadProgress::class, [self::class, 'handleProgress']);
        $events->listen(UpdateDownloaded::class, [self::class, 'handleDownloaded']);
        $events->listen(Error::class, [self::class, 'handleError']);
    }
}
