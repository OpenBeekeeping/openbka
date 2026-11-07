<?php

namespace App\Filament\Widgets;

use App\Enums\MemberStatus;
use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Support\Access;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

/** Branches ranked by active members. Hidden from branch admins, who only see one branch. */
class MembersByBranch extends Widget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.members-by-branch';

    public static function canView(): bool
    {
        return Access::managesAssociation();
    }

    /** @return array{branches: Collection<int, Branch>, url: string} */
    protected function getViewData(): array
    {
        return [
            'branches' => Branch::query()
                ->withCount(['members' => fn ($q) => $q->where('status', MemberStatus::Active)])
                ->orderByDesc('members_count')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'url' => BranchResource::getUrl(),
        ];
    }
}
