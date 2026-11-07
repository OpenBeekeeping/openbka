<?php

namespace App\Console\Commands;

use App\Auth\MagicLink;
use App\Enums\Role;
use App\Models\Association;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\info;
use function Laravel\Prompts\text;

#[Signature('openbka:install {--association=} {--name=} {--email=} {--branch=Main branch}')]
#[Description('Set up a self-hosted OpenBKA: create your association and its first admin')]
class Install extends Command
{
    public function handle(): int
    {
        if (Association::exists()) {
            $this->components->warn('OpenBKA is already set up. To sign in, run: php artisan openbka:login <email>');

            return self::SUCCESS;
        }

        $associationName = $this->option('association') ?: text('Association name', required: true);
        $name = $this->option('name') ?: text('Your name', required: true);
        $email = $this->option('email') ?: text('Your email', required: true, validate: fn ($v) => filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.');

        [$user, $association] = DB::transaction(function () use ($associationName, $name, $email) {
            $user = User::firstOrCreate(['email' => $email], ['name' => $name, 'email_verified_at' => now()]);
            $association = Association::create(['name' => $associationName]);
            $association->users()->attach($user, ['role' => Role::Owner->value]);
            $association->branches()->create(['name' => $this->option('branch')]);

            return [$user, $association];
        });

        info("{$association->name} is ready.");
        $this->line('Open this link to sign in (it works once, for '.config('openbka.magic_link_ttl').' minutes):');
        $this->newLine();
        $this->line(MagicLink::create($user));
        $this->newLine();
        $this->line('Then add a passkey from your profile so you can sign in without email.');

        return self::SUCCESS;
    }
}
