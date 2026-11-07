<?php

namespace App\Filament\Pages;

use App\Auth\MagicLink;
use App\Enums\Role;
use App\Models\Association;
use App\Models\User;
use App\Support\Access;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

/** People who can sign in and manage this association. */
class Admins extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return Access::managesAssociation();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([EmbeddedTable::make()]);
    }

    protected function association(): Association
    {
        return Filament::getTenant();
    }

    public function table(Table $table): Table
    {
        $branches = $this->association()->branches()->pluck('name', 'id');

        return $table
            ->query(fn () => $this->association()->users()->getQuery())
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('pivot.role')
                    ->label('Role')
                    ->formatStateUsing(fn ($state) => Role::from($state)->getLabel())
                    ->badge(),
                TextColumn::make('pivot.branch_id')
                    ->label('Branch')
                    ->formatStateUsing(fn ($state) => $branches[$state] ?? '')
                    ->placeholder('All branches'),
            ])
            ->headerActions([
                Action::make('invite')
                    ->label('invite admin')
                    ->icon(Heroicon::OutlinedUserPlus)
                    ->schema($this->roleFields(withPerson: true))
                    ->action(function (array $data) {
                        $association = $this->association();
                        $user = User::firstOrCreate(['email' => $data['email']], ['name' => $data['name']]);

                        if ($association->users()->whereKey($user)->exists()) {
                            Notification::make()->title('Error: '.$data['email'].' is already an admin here.')->danger()->send();

                            return;
                        }

                        $association->users()->attach($user, $this->pivotData($data));
                        MagicLink::send($user, Filament::getUrl($association));

                        Notification::make()->title('Invite sent to '.$data['email'])->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('changeRole')
                    ->label('change role')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->fillForm(fn (User $record) => [
                        'role' => $record->pivot->role,
                        'branch_id' => $record->pivot->branch_id,
                    ])
                    ->schema($this->roleFields())
                    ->visible(fn (User $record) => $this->canChange($record))
                    ->action(fn (User $record, array $data) => $this->association()->users()->updateExistingPivot($record, $this->pivotData($data))),
                Action::make('remove')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $this->canChange($record))
                    ->action(fn (User $record) => $this->association()->users()->detach($record)),
            ]);
    }

    /** You can't change yourself, and only owners can change other owners. */
    protected function canChange(User $record): bool
    {
        if ($record->is(auth()->user())) {
            return false;
        }

        return $record->pivot->role !== Role::Owner->value || Access::role() === Role::Owner;
    }

    protected function roleFields(bool $withPerson = false): array
    {
        $roles = collect(Role::cases())
            ->when(Access::role() !== Role::Owner, fn ($roles) => $roles->reject(fn ($r) => $r === Role::Owner))
            ->mapWithKeys(fn (Role $r) => [$r->value => $r->getLabel()]);

        return array_filter([
            $withPerson ? TextInput::make('name')->required()->maxLength(255) : null,
            $withPerson ? TextInput::make('email')->email()->required()->maxLength(255) : null,
            Select::make('role')->options($roles)->default(Role::Admin->value)->required()->live(),
            Select::make('branch_id')
                ->label('Branch')
                ->options(fn () => $this->association()->branches()->pluck('name', 'id'))
                ->visible(fn (Get $get) => $get('role') === Role::BranchAdmin->value)
                ->required(fn (Get $get) => $get('role') === Role::BranchAdmin->value),
        ]);
    }

    protected function pivotData(array $data): array
    {
        $role = Role::from($data['role']);

        // Only owners can create owners.
        if ($role === Role::Owner && Access::role() !== Role::Owner) {
            $role = Role::Admin;
        }

        $branchId = $role === Role::BranchAdmin ? $data['branch_id'] : null;

        abort_if($branchId && ! $this->association()->branches()->whereKey($branchId)->exists(), 403);

        return ['role' => $role->value, 'branch_id' => $branchId];
    }
}
