<?php

namespace App\Billing;

use App\Http\Middleware\EnsureAssociationHasPlan;
use App\Models\Association;
use Closure;
use Filament\Billing\Providers\Contracts\BillingProvider;
use Filament\Facades\Filament;

/** Hosted mode: each association pays for its own subscription. */
class StripeBillingProvider implements BillingProvider
{
    public function getRouteAction(): string|Closure|array
    {
        return function () {
            /** @var Association $association */
            $association = Filament::getTenant();
            $returnUrl = Filament::getUrl($association);

            if ($association->subscribed('default')) {
                return $association->redirectToBillingPortal($returnUrl);
            }

            return $association
                ->newSubscription('default', config('openbka.billing.price'))
                ->allowPromotionCodes()
                ->checkout([
                    'success_url' => $returnUrl,
                    'cancel_url' => $returnUrl,
                ]);
        };
    }

    public function getSubscribedMiddleware(): string
    {
        return EnsureAssociationHasPlan::class;
    }
}
