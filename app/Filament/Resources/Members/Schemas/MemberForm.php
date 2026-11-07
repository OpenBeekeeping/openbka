<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Enums\MemberStatus;
use App\Support\Access;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Member')->columns(2)->schema([
                TextInput::make('first_name')->required()->maxLength(255),
                TextInput::make('last_name')->required()->maxLength(255),
                Select::make('branch_id')
                    ->label('Branch')
                    ->relationship(
                        'branch',
                        'name',
                        modifyQueryUsing: fn (Builder $query) => Access::scopeToBranch($query, 'id'),
                    )
                    ->default(fn () => Access::branchId())
                    ->visible(fn (): bool => Filament::getTenant()->hasBranches())
                    ->required()
                    ->preload(),
                TextInput::make('membership_number')->maxLength(50),
                Select::make('status')->options(MemberStatus::class)->default(MemberStatus::Active)->required(),
                DatePicker::make('joined_on'),
                DatePicker::make('renews_on'),
            ]),
            Section::make('Contact')->columns(2)->schema([
                TextInput::make('email')->email(),
                TextInput::make('phone')->tel(),
                TextInput::make('address_line_1'),
                TextInput::make('address_line_2'),
                TextInput::make('town'),
                TextInput::make('postcode'),
            ]),
            Textarea::make('notes')->columnSpanFull(),
        ]);
    }
}
