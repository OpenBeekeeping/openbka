<?php

namespace App\Filament\Resources\Members\Tables;

use App\Enums\MemberStatus;
use App\Support\Access;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('membership_number')->label('No.')->searchable()->sortable(),
                TextColumn::make('last_name')
                    ->label('Name')
                    ->formatStateUsing(fn ($record) => $record->name)
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('branch.name')->sortable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('renews_on')->date()->sortable()->toggleable(),
            ])
            ->defaultSort('last_name')
            ->filters([
                SelectFilter::make('branch')
                    ->relationship('branch', 'name', fn (Builder $query) => Access::scopeToBranch($query, 'id'))
                    ->visible(fn () => Access::managesAssociation()),
                SelectFilter::make('status')->options(MemberStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
