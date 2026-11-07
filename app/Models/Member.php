<?php

namespace App\Models;

use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Member extends Model
{
    protected $fillable = [
        'branch_id', 'membership_number', 'first_name', 'last_name', 'email', 'phone',
        'address_line_1', 'address_line_2', 'town', 'postcode',
        'status', 'joined_on', 'renews_on', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MemberStatus::class,
            'joined_on' => 'date',
            'renews_on' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Member $member) {
            $member->ensureBranchFitsAssociation();
        });
    }

    /**
     * An association with branches keeps every member in one of its own branches.
     * An association without branches keeps its members directly, with no branch.
     */
    public function ensureBranchFitsAssociation(): void
    {
        if ($this->branch_id === null) {
            $associationHasBranches = Branch::withoutGlobalScopes()
                ->where('association_id', $this->association_id)
                ->exists();

            if ($associationHasBranches) {
                throw ValidationException::withMessages(['branch_id' => 'Choose a branch for this member.']);
            }

            return;
        }

        $branch = Branch::withoutGlobalScopes()->find($this->branch_id);

        if (! $branch || $branch->association_id !== $this->association_id) {
            throw ValidationException::withMessages(['branch_id' => 'Choose a branch in this association.']);
        }
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim("{$this->first_name} {$this->last_name}"));
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
