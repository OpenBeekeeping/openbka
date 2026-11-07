<?php

namespace App\Filament\Pages\Auth;

use App\Auth\MagicLink;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Exposed;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Timebox;

/**
 * Passwordless sign-in: enter your email and we send a one-time link.
 * Passkeys are offered below the form by the passkeys plugin.
 */
class Login extends BaseLogin
{
    public bool $linkSent = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getEmailFormComponent()->autofocus(),
        ]);
    }

    #[Exposed]
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $email = $this->form->getState()['email'];

        // Same response time and message whether or not the account exists.
        app(Timebox::class)->call(function () use ($email) {
            if ($user = User::where('email', $email)->first()) {
                MagicLink::send($user);
            }
        }, 400_000);

        $this->linkSent = true;

        Notification::make()
            ->title('Check your email')
            ->body('If '.$email.' has an account, we have sent it a sign-in link.')
            ->success()
            ->send();

        return null;
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Sign in';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->linkSent
            ? 'Link sent. You can close this tab once you have opened the email.'
            : 'We will email you a sign-in link. No password needed.';
    }

    protected function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction()->label('Email me a link');
    }
}
