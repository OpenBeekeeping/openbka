<?php

namespace Tests\Feature;

class HomePageTest extends OpenBkaTestCase
{
    public function test_hosted_mode_shows_the_home_page(): void
    {
        config(['openbka.mode' => 'hosted']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Start a free trial')
            ->assertSee('/app/register', escape: false);
    }

    public function test_self_hosted_mode_goes_straight_to_sign_in(): void
    {
        config(['openbka.mode' => 'self-hosted']);

        $this->get('/')->assertRedirect('/app');
    }
}
