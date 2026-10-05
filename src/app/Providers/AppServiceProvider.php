<?php

namespace App\Providers;

use App\Models\Opportunity;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\User;

use App\Observers\OpportunityObserver;
use App\Observers\QuotationObserver;
use App\Observers\ShipmentObserver;
use App\Observers\UserObserver;

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
        /*
         * User notifications.
         */
        User::observe(
            UserObserver::class
        );

        /*
         * Opportunity notifications.
         */
        Opportunity::observe(
            OpportunityObserver::class
        );

        /*
         * Quotation notifications.
         */
        Quotation::observe(
            QuotationObserver::class
        );

        /*
         * Shipment notifications.
         */
        Shipment::observe(
            ShipmentObserver::class
        );
    }
}
