<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Observers\PurchaseOrderObserver;
use App\Observers\SalesOrderObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load mail config from database
        try {
            $settings = cache()->remember('app_settings_mail', 3600, function () {
                return AppSetting::whereIn('key', [
                    'mail_host',
                    'mail_port',
                    'mail_username',
                    'mail_password',
                    'mail_encryption',
                    'mail_from_name',
                ])->pluck('value', 'key');
            });

            if ($settings->isNotEmpty()) {
                config([
                    'mail.mailers.smtp.host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                    'mail.mailers.smtp.port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
                    'mail.mailers.smtp.username' => $settings['mail_username'] ?? '',
                    'mail.mailers.smtp.password' => $settings['mail_password'] ?? '',
                    'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? 'tls',
                    'mail.from.address' => $settings['mail_username'] ?? config('mail.from.address'),
                    'mail.from.name' => $settings['mail_from_name'] ?? 'PT. Nusantara Abadi Jaya',
                ]);
            }
        } catch (\Exception $e) {
            // If database not available (e.g., during migration), use default config
        }

        SalesOrder::observe(SalesOrderObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);

        Vite::prefetch(concurrency: 3);
    }
}
