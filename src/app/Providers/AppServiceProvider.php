<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\User;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\Opportunity;

use App\Observers\UserObserver;
use App\Observers\QuotationObserver;
use App\Observers\ShipmentObserver;
use App\Observers\OpportunityObserver;

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
        User::observe(UserObserver::class);
        Quotation::observe(QuotationObserver::class);
        Shipment::observe(ShipmentObserver::class);
        Opportunity::observe(OpportunityObserver::class);
    }
}
