<?php

namespace App\Providers;

use App\Models\Association;
use App\Support\Mode;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
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

        // UK dates: 20 Nov 2026.
        Table::configureUsing(fn (Table $table) => $table
            ->defaultDateDisplayFormat('j M Y')
            ->defaultDateTimeDisplayFormat('j M Y, H:i'));
        Schema::configureUsing(fn (Schema $schema) => $schema
            ->defaultDateDisplayFormat('j M Y')
            ->defaultDateTimeDisplayFormat('j M Y, H:i'));
    }
}
