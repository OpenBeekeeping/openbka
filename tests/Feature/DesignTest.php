<?php

namespace Tests\Feature;

use App\Support\Design;
use Filament\Support\Colors\Color;
use Tests\TestCase;

class DesignTest extends TestCase
{
    public function test_every_palette_has_all_filament_shades(): void
    {
        foreach (Design::colors() as $name => $palette) {
            $this->assertSame([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950], array_keys($palette), "{$name} palette");
        }
    }

    public function test_brand_shades_match_the_design_tokens(): void
    {
        $primary = Design::colors()['primary'];

        $this->assertSame(Color::convertToOklch('#ffd60a'), $primary[500]);
        $this->assertSame(Color::convertToOklch(Design::token('brand-ink', 'light')), $primary[600]);
    }

    public function test_the_panel_loads_the_design_system(): void
    {
        $this->get('/app/login')
            ->assertOk()
            ->assertSee('design/css/openbeekeeping.css')
            ->assertSee('design/css/theme.css')
            ->assertSee('css/openbka.css')
            ->assertSee('design/logos/openbka-lockup-on-dark.svg');
    }
}
