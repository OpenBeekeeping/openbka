<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('openbka:sync-design {path : Path to the Bickerton Beekeeping design-system folder (MonoRepo/brand/design-system)}')]
#[Description('Copy the Bickerton Beekeeping design tokens and favicon into OpenBKA')]
class SyncDesign extends Command
{
    /**
     * Token files copied verbatim. fonts.css and base.css are left out: OpenBKA
     * ships DM Sans instead of Champ, and Filament provides its own resets.
     *
     * @var list<string>
     */
    protected array $tokens = ['colors', 'typography', 'spacing', 'effects'];

    public function handle(): int
    {
        $source = rtrim($this->argument('path'), '/');

        if (! File::exists("{$source}/tokens/colors.css")) {
            $this->components->error("No Bickerton design system found at {$source}.");

            return self::FAILURE;
        }

        File::ensureDirectoryExists(public_path('bickerton/css'));
        File::ensureDirectoryExists(public_path('bickerton/brand'));

        foreach ($this->tokens as $file) {
            File::copy("{$source}/tokens/{$file}.css", public_path("bickerton/css/{$file}.css"));
        }

        File::copy("{$source}/assets/logos/favicon.svg", public_path('bickerton/brand/favicon.svg'));

        $this->components->info('Synced the Bickerton design system. Check the app, then commit public/bickerton.');

        return self::SUCCESS;
    }
}
