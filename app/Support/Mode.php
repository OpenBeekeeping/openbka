<?php

namespace App\Support;

class Mode
{
    public static function hosted(): bool
    {
        return config('openbka.mode') === 'hosted';
    }

    public static function selfHosted(): bool
    {
        return ! static::hosted();
    }
}
