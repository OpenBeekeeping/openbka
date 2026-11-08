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

    public function test_palettes_use_the_bickerton_tokens(): void
    {
        $colors = Design::colors();

        $this->assertSame('#F0C234', Design::token('brand'));
        $this->assertSame(Color::convertToOklch('#F0C234'), $colors['primary'][500]);
        $this->assertSame(Color::convertToOklch(Design::token('honey-800')), $colors['primary'][600]);
        $this->assertSame(Color::convertToOklch(Design::token('wax-900')), $colors['gray'][950]);
    }

    public function test_the_panel_loads_the_bickerton_theme(): void
    {
        $this->get('/app/login')
            ->assertOk()
            ->assertSee('bickerton/css/colors.css')
            ->assertSee('bickerton/css/fonts.css')
            ->assertSee('css/openbka.css')
            ->assertSee('ob-bee');
    }
}
