<?php

namespace App\Console\Commands;

use App\Enums\NotificationCategory;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\QuotationExpiredNotification;
use App\Services\NotificationManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Native\Desktop\Facades\Notification as NativeNotification;

class ExpireQuotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotations:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire pending quotations that have passed their validity date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired quotations...');

        $now = now();

        $count = Quotation::where('status', 'pending')
            ->where('valid_until', '<', $now->startOfDay())
            ->update(['status' => 'rejected']);


        if ($count > 0) {
            $this->info("Expired {$count} quotations.");
            Log::info("Expired {$count} quotations via scheduled task.");

            $this->dispatchNotifications($count);
        } else {
            $this->info('No expired quotations found.');
        }

        return Command::SUCCESS;
    }

    private function dispatchNotifications(int $count): void
    {
        try {
            $payload = [
                'title' => 'Cotizaciones vencidas',
                'message' => "Se han cancelado automáticamente {$count} cotizaciones vencidas.",
                'count' => $count,
                'occurred_at' => now()->toIso8601String(),
            ];

            if (!NotificationManager::shouldNotify(NotificationCategory::System)) {
                return;
            }

            $recipients = User::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->permission('read quotations')
                        ->orWhereHas('roles', fn($q) => $q->where('name', 'admin'));
                })
                ->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new QuotationExpiredNotification($payload));
            }

            NativeNotification::title($payload['title'])
                ->message($payload['message'])
                ->show();
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch quotation expired notifications', [
                'count' => $count,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
