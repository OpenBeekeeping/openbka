<?php

namespace App\Models;

use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

class Association extends Model implements HasCurrentTenantLabel
{
    use Billable;

    protected $fillable = ['name', 'slug', 'email', 'website', 'charity_number', 'trial_ends_at'];

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Association $association) {
            $association->slug ??= static::uniqueSlug($association->name);
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'association';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /** Associations with branches keep every member in a branch; those without keep members directly. */
    public function hasBranches(): bool
    {
        return $this->branches()->exists();
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role', 'branch_id'])
            ->withTimestamps();
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Association';
    }

    /** Hosted mode: is this association allowed in (trial or active subscription)? */
    public function hasActivePlan(): bool
    {
        return $this->onGenericTrial() || $this->subscribed('default');
    }
}
