<?php

namespace App\Http\Controllers\Auth;

use App\Auth\MagicLink;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MagicLinkController
{
    /**
     * Show a confirm button rather than signing in on GET, so email
     * security scanners that open links don't use them up.
     */
    public function show(string $token): View
    {
        return view('auth.magic-link', [
            'token' => $token,
            'valid' => MagicLink::isValid($token),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $data = MagicLink::consume($token);
        $user = $data ? User::find($data['user_id']) : null;

        if (! $user) {
            return redirect()->route('magic-link.show', ['token' => $token]);
        }

        // Opening the emailed link proves they own the address.
        if (! $user->email_verified_at) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->to($data['redirect_to'] ?? Filament::getDefaultPanel()->getUrl());
    }
}
