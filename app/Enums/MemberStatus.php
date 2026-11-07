<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum MemberStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Lapsed = 'lapsed';
    case Resigned = 'resigned';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Lapsed => 'warning',
            self::Resigned => 'gray',
        };
    }
}
