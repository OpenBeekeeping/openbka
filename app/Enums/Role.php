<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case Owner = 'owner';
    case Admin = 'admin';
    case BranchAdmin = 'branch_admin';

    public function getLabel(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Association admin',
            self::BranchAdmin => 'Branch admin',
        };
    }
}
