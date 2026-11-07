<?php

namespace App\Support;

use App\Enums\Role;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

/** Role checks for the signed-in user within the current association. */
class Access
{
    public static function role(): ?Role
    {
        $association = Filament::getTenant();

        return $association ? auth()->user()?->roleIn($association) : null;
    }

    public static function managesAssociation(): bool
    {
        return in_array(static::role(), [Role::Owner, Role::Admin], true);
    }

    /** The branch a branch admin is limited to, or null when they can see every branch. */
    public static function branchId(): ?int
    {
        $association = Filament::getTenant();

        return $association ? auth()->user()?->branchIdIn($association) : null;
    }

    public static function scopeToBranch(Builder $query, string $column = 'branch_id'): Builder
    {
        if (static::role() === Role::BranchAdmin) {
            // A branch admin with no branch set sees nothing rather than everything.
            $query->where($column, static::branchId() ?? 0);
        }

        return $query;
    }
}
