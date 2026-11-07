<?php

namespace App\Filament\Pages\Tenancy;

use App\Enums\Role;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class EditAssociation extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Association settings';
    }

    public static function canView(Model $tenant): bool
    {
        return in_array(auth()->user()->roleIn($tenant), [Role::Owner, Role::Admin], true);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email(),
            TextInput::make('website')->url(),
            TextInput::make('charity_number')->label('Charity number'),
        ]);
    }
}
