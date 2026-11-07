<?php

namespace App\Filament\Pages\Tenancy;

use App\Enums\Role;
use App\Models\Association;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Hosted mode: a signed-in user adds another association. */
class RegisterAssociation extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Add an association';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Association name')->required()->maxLength(255),
        ]);
    }

    protected function handleRegistration(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $association = Association::create([
                'name' => $data['name'],
                'trial_ends_at' => now()->addDays(config('openbka.billing.trial_days')),
            ]);
            $association->users()->attach(auth()->user(), ['role' => Role::Owner->value]);
            $association->branches()->create(['name' => 'Main branch']);

            return $association;
        });
    }
}
