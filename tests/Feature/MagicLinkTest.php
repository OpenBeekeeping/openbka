<?php

namespace Tests\Feature;

use App\Auth\MagicLink;
use App\Filament\Pages\Auth\Login;
use App\Models\User;
use App\Notifications\SignInLink;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

class MagicLinkTest extends OpenBkaTestCase
{
    public function test_login_form_emails_a_link_to_known_users(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        Livewire::test(Login::class)->set('data.email', $user->email)->call('authenticate');

        Notification::assertSentTo($user, SignInLink::class);
    }

    public function test_login_form_gives_no_hint_for_unknown_emails(): void
    {
        Notification::fake();

        Livewire::test(Login::class)
            ->set('data.email', 'nobody@example.com')
            ->call('authenticate')
            ->assertSet('linkSent', true);

        Notification::assertNothingSent();
    }

    public function test_opening_a_link_does_not_sign_in_until_confirmed(): void
    {
        $user = User::factory()->unverified()->create();
        $url = MagicLink::create($user);

        $this->get($url)->assertOk()->assertSee('Continue');
        $this->assertGuest();

        $this->post($url)->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_a_link_works_only_once(): void
    {
        $user = User::factory()->create();
        $url = MagicLink::create($user);

        $this->post($url);
        auth()->logout();

        $this->post($url);
        $this->assertGuest();
        $this->get($url)->assertSee('expired');
    }

    public function test_links_expire(): void
    {
        $url = MagicLink::create(User::factory()->create());

        $this->travel(16)->minutes();

        $this->post($url);
        $this->assertGuest();
    }
}
