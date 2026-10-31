<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;

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
        \Illuminate\Support\Facades\Gate::define(
            'approve-deal-agreement',
            fn (\App\Models\User $user): bool =>
                $user->hasRole('super-admin')
                || $user->isAbleTo('deals-approve')
        );

        Relation::morphMap([
            'customer' => \App\Models\Customer::class,
            'shipment' => \App\Models\Shipment::class,
            'quotation' => \App\Models\Quotation::class,
        ]);

        //
    }
}
