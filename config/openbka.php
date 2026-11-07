<?php

return [

    /*
    | "self-hosted": one association, set up with `php artisan openbka:install`.
    |               Public sign-up and billing are switched off.
    | "hosted":     the managed service on openbka.org. Anyone can sign up a new
    |               association, and each association pays through Stripe.
    */
    'mode' => env('OPENBKA_MODE', 'self-hosted'),

    'billing' => [
        // Stripe price ID for the hosted plan.
        'price' => env('OPENBKA_STRIPE_PRICE'),
        'trial_days' => (int) env('OPENBKA_TRIAL_DAYS', 30),
    ],

    // How long a sign-in link stays valid, in minutes.
    'magic_link_ttl' => 15,

];
