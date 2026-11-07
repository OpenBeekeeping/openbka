<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Filament\Pages\Auth\Register;
use App\Models\Association;
use App\Models\User;
use App\Notifications\SignInLink;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

class HostedSignUpTest extends OpenBkaTestCase
{
    public function test_sign_up_creates_an_association_on_a_trial(): void
    {
        Notification::fake();
        config(['openbka.mode' => 'hosted']);

        Livewire::test(Register::class)
            ->set('data.association', 'Devon BKA')
            ->set('data.name', 'Sam')
            ->set('data.email', 'sam@example.com')
            ->call('register')
            ->assertSet('linkSent', true);

        $association = Association::firstWhere('name', 'Devon BKA');
        $user = User::firstWhere('email', 'sam@example.com');

        $this->assertSame(Role::Owner, $user->roleIn($association));
        $this->assertCount(1, $association->branches);
        $this->assertTrue($association->onGenericTrial());
        $this->assertTrue($association->hasActivePlan());
        Notification::assertSentTo($user, SignInLink::class);
    }

    public function test_plan_ends_when_the_trial_does(): void
    {
        $association = Association::create(['name' => 'Expired', 'trial_ends_at' => now()->subDay()]);

        $this->assertFalse($association->hasActivePlan());
    }
}
