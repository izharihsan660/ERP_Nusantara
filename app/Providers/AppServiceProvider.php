<?php

namespace App\Providers;

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
        SalesOrder::observe(SalesOrderObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);

        Vite::prefetch(concurrency: 3);
    }
}
