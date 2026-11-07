<?php

namespace App\Filament\Widgets;

use App\Enums\MemberStatus;
use App\Filament\Resources\Members\MemberResource;
use App\Models\Member;
use App\Support\Access;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

/** The next renewals (and any overdue), soonest first, linking to each member. */
class RenewalsDue extends Widget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.renewals-due';

    protected int|string|array $columnSpan = 'full';

    /** @return array{members: Collection<int, Member>, url: string} */
    protected function getViewData(): array
    {
        return [
            'members' => Access::scopeToBranch(Member::query())
                ->with('branch')
                ->where('status', MemberStatus::Active)
                ->whereNotNull('renews_on')
                ->where('renews_on', '<=', today()->addDays(30))
                ->orderBy('renews_on')
                ->limit(8)
                ->get(),
            'url' => MemberResource::getUrl(),
        ];
    }
}
