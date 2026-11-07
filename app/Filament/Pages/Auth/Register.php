<?php

namespace App\Filament\Pages\Auth;

use App\Auth\MagicLink;
use App\Enums\Role;
use App\Models\Association;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\RegistrationResponse;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Exposed;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

/**
 * Hosted mode only: sign up a new association and its first admin.
 * No password: we email a sign-in link to confirm the address.
 */
class Register extends BaseRegister
{
    public bool $linkSent = false;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('association')
                ->label('Association name')
                ->placeholder('e.g. Somerset Beekeepers Association')
                ->required()
                ->maxLength(255),
            $this->getNameFormComponent()->label('Your name'),
            TextInput::make('email')
                ->label('Your email')
                ->email()
                ->required()
                ->maxLength(255),
        ]);
    }

    #[Exposed]
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(3);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        $association = DB::transaction(function () use ($data) {
            $user = User::firstOrCreate(['email' => $data['email']], ['name' => $data['name']]);

            $association = Association::create([
                'name' => $data['association'],
                'trial_ends_at' => now()->addDays(config('openbka.billing.trial_days')),
            ]);
            $association->users()->attach($user, ['role' => Role::Owner->value]);
            $association->branches()->create(['name' => 'Main branch']);

            return [$user, $association];
        });

        [$user, $association] = $association;

        MagicLink::send($user, route('filament.app.pages.dashboard', ['tenant' => $association]));

        $this->linkSent = true;

        Notification::make()
            ->title('Check your email')
            ->body('We have sent a sign-in link to '.$data['email'].'. Open it to finish setting up '.$association->name.'.')
            ->success()
            ->persistent()
            ->send();

        return null;
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Start your free trial';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->linkSent
            ? 'Link sent. Check your email to continue.'
            : parent::getSubheading();
    }

    public function getRegisterFormAction(): Action
    {
        return parent::getRegisterFormAction()->label('create association');
    }
}
