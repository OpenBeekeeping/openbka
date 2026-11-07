<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Branch extends Model
{
    protected $fillable = ['name', 'slug', 'email', 'description'];

    protected static function booted(): void
    {
        static::saving(function (Branch $branch) {
            $branch->slug = $branch->slug ?: Str::slug($branch->name);
        });
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
