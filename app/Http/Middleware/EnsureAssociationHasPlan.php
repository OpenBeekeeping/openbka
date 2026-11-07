<?php

namespace App\Http\Middleware;

use App\Models\Association;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Hosted mode: send associations whose trial has ended to Stripe Checkout. */
class EnsureAssociationHasPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Association|null $association */
        $association = Filament::getTenant();

        if ($association && ! $association->hasActivePlan()) {
            return redirect(Filament::getTenantBillingUrl($association));
        }

        return $next($request);
    }
}
