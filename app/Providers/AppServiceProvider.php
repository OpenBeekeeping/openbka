<?php

namespace App\Providers;

use App\Models\Association;
use App\Support\Mode;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Billing only exists on the hosted service.
        if (Mode::selfHosted()) {
            Cashier::ignoreRoutes();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The association, not the user, is the Stripe customer.
        Cashier::useCustomerModel(Association::class);
    }
}
