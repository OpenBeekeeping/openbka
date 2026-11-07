<?php

namespace App\Support;

use Filament\Support\Colors\Color;

/**
 * Filament colour palettes built from the Bickerton Beekeeping colour tokens
 * (public/bickerton/css/colors.css, copied in by `php artisan openbka:sync-design`).
 *
 * The brand is light-only. Filament reads shade 600 for coloured text on light
 * backgrounds and 500 for fills, so those carry the matching brand tokens.
 */
class Design
{
    /** @var array<string, string>|null */
    protected static ?array $tokens = null;

    /** The hex value of a colour token, following var() aliases. */
    public static function token(string $name): string
    {
        static::$tokens ??= static::parse(file_get_contents(public_path('bickerton/css/colors.css')));

        $value = static::$tokens[$name] ?? throw new \InvalidArgumentException("Unknown colour token --{$name}.");

        return preg_match('/^var\(--([\w-]+)\)$/', $value, $m) ? static::token($m[1]) : $value;
    }

    /** @return array<string, array<int, string>> */
    public static function colors(): array
    {
        return [
            'primary' => static::family('honey', 'brand', [600 => 'honey-800']),
            'gray' => static::wax(),
            'success' => static::family('meadow', 'success', [50 => 'surface-meadow-subtle', 600 => 'meadow-700']),
            'warning' => static::family('honey', 'warning', [50 => 'honey-50', 600 => 'honey-800']),
            'danger' => static::family('terracotta', 'danger', [50 => 'danger-subtle', 600 => 'terracotta-700']),
            'info' => static::family('sky', 'info', [50 => 'surface-sky-subtle', 600 => 'sky-300']),
        ];
    }

    /**
     * A full 50–950 palette: generated from the family's core colour, with the
     * brand's own shades put back wherever they exist.
     *
     * @param  string  $core  token the generated shades are based on
     * @param  array<int, string>  $overrides  shade => token name
     * @return array<int, string>
     */
    protected static function family(string $family, string $core, array $overrides = []): array
    {
        $palette = Color::generatePalette(static::token($core));

        foreach (array_keys($palette) as $shade) {
            if (isset(static::$tokens["{$family}-{$shade}"])) {
                $palette[$shade] = static::token("{$family}-{$shade}");
            }
        }

        foreach ($overrides as $shade => $token) {
            $palette[$shade] = static::token($token);
        }

        return static::oklch($palette);
    }

    /** Warm "wax" neutrals in place of Filament's cold greys. @return array<int, string> */
    protected static function wax(): array
    {
        return static::oklch([
            50 => static::token('wax-50'),
            100 => static::token('wax-100'),
            200 => static::token('wax-200'),
            300 => static::token('wax-300'),
            400 => static::token('wax-400'),
            500 => static::token('text-muted'),
            600 => static::token('wax-600'),
            700 => static::token('wax-700'),
            800 => static::token('wax-800'),
            900 => static::token('wax-900'),
            950 => static::token('wax-900'),
        ]);
    }

    /** @return array<string, string> */
    protected static function parse(string $css): array
    {
        preg_match_all('/--([\w-]+):\s*([^;]+);/', $css, $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn (array $m): array => [$m[1] => trim($m[2])])->all();
    }

    /**
     * @param  array<int, string>  $palette
     * @return array<int, string>
     */
    protected static function oklch(array $palette): array
    {
        ksort($palette);

        return array_map(
            fn (string $c): string => str_starts_with($c, 'oklch') ? $c : Color::convertToOklch($c),
            $palette,
        );
    }
}
