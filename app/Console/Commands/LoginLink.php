<?php

namespace App\Console\Commands;

use App\Auth\MagicLink;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('openbka:login {email}')]
#[Description('Print a one-time sign-in link (useful before email is set up)')]
class LoginLink extends Command
{
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->components->error('No user with that email.');

            return self::FAILURE;
        }

        $this->line(MagicLink::create($user));

        return self::SUCCESS;
    }
}
