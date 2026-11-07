<?php

namespace App\Support;

use Filament\Support\Colors\Color;

/**
 * Filament colour palettes built from the Open Beekeeping design tokens
 * (resources/design/tokens.json, copied in by scripts/sync-design.php).
 *
 * Filament reads shade 400 for text on dark backgrounds and 600 on light ones,
 * so those two shades carry each token's exact dark and light values.
 */
class Design
{
    protected static ?array $tokens = null;

    public static function token(string $name, string $theme = 'dark'): string
    {
        static::$tokens ??= collect(json_decode(file_get_contents(resource_path('design/tokens.json')), true)['color']['tokens'])
            ->mapWithKeys(fn ($t) => [$t['name'] => $t['value']])
            ->all();

        return static::$tokens[$name][$theme];
    }

    public static function colors(): array
    {
        return [
            'primary' => static::primary(),
            'gray' => static::gray(),
            'success' => static::status('ok'),
            'warning' => static::status('warn'),
            'danger' => static::status('alert'),
            'info' => static::gray(),
        ];
    }

    /** Pollen yellow. Fills use 400/500; brand-ink (light theme text) sits at 600 and up. */
    protected static function primary(): array
    {
        return static::oklch(array_replace(static::generated(static::token('brand')), [
            300 => static::token('brand-hover'),
            400 => static::token('brand'),
            500 => static::token('brand'),
            600 => static::token('brand-ink', 'light'),
        ]));
    }

    /** Greys follow the surface, line and ink tokens. */
    protected static function gray(): array
    {
        return static::oklch([
            50 => static::token('surface', 'light'),
            100 => static::token('surface-sunken', 'light'),
            200 => static::token('line', 'light'),
            300 => '#c3c7c0',
            400 => static::token('ink-muted'),
            500 => static::token('line-strong', 'light'),
            600 => static::token('ink-muted', 'light'),
            700 => static::token('line'),
            800 => static::token('surface-hover'),
            900 => static::token('surface-raised'),
            950 => static::token('surface'),
        ]);
    }

    protected static function status(string $name): array
    {
        return static::oklch(array_replace(static::generated(static::token($name)), [
            400 => static::token($name),
            600 => static::token($name, 'light'),
        ]));
    }

    protected static function generated(string $hex): array
    {
        return Color::generatePalette($hex);
    }

    protected static function oklch(array $palette): array
    {
        ksort($palette);

        return array_map(
            fn (string $c) => str_starts_with($c, 'oklch') ? $c : Color::convertToOklch($c),
            $palette,
        );
    }
}
