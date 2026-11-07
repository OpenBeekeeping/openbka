<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Laravel\Passkeys\PasskeyAuthenticatable;

class User extends Authenticatable implements FilamentUser, HasTenants, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable;

    protected $fillable = ['name', 'email'];

    protected $hidden = ['remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function associations(): BelongsToMany
    {
        return $this->belongsToMany(Association::class)
            ->withPivot(['role', 'branch_id'])
            ->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->associations;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->associations()->whereKey($tenant)->exists();
    }

    public function roleIn(Association $association): ?Role
    {
        $pivot = $this->associations->firstWhere('id', $association->id)?->pivot;

        return $pivot ? Role::from($pivot->role) : null;
    }

    /** The branch this user is limited to in an association, or null for all branches. */
    public function branchIdIn(Association $association): ?int
    {
        if ($this->roleIn($association) !== Role::BranchAdmin) {
            return null;
        }

        return $this->associations->firstWhere('id', $association->id)?->pivot->branch_id;
    }
}
