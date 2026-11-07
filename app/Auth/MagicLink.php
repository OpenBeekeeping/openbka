<?php

namespace App\Auth;

use App\Models\User;
use App\Notifications\SignInLink;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * One-time sign-in links. The token is random, only its hash is stored,
 * and it can be used once before it expires.
 */
class MagicLink
{
    public static function send(User $user, ?string $redirectTo = null): void
    {
        $user->notify(new SignInLink(static::create($user, $redirectTo)));
    }

    public static function create(User $user, ?string $redirectTo = null): string
    {
        $token = Str::random(64);

        Cache::put(static::key($token), [
            'user_id' => $user->id,
            'redirect_to' => $redirectTo,
        ], now()->addMinutes(config('openbka.magic_link_ttl')));

        return route('magic-link.show', ['token' => $token]);
    }

    /** Returns the link's data and deletes it, or null if it is invalid or used. */
    public static function consume(string $token): ?array
    {
        return Cache::pull(static::key($token));
    }

    public static function isValid(string $token): bool
    {
        return Cache::has(static::key($token));
    }

    protected static function key(string $token): string
    {
        return 'magic-link:'.hash('sha256', $token);
    }
}
