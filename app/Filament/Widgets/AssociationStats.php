<?php

namespace App\Filament\Widgets;

use App\Enums\MemberStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Support\Access;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

/** Headline numbers, each with a short line saying what it counts (as in the CICMB admin). */
class AssociationStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    /** @return list<Stat> */
    protected function getStats(): array
    {
        $members = fn (): Builder => Access::scopeToBranch(Member::query());

        $active = $members()->where('status', MemberStatus::Active)->count();
        $joinedThisMonth = $members()->where('joined_on', '>=', today()->startOfMonth())->count();
        $renewing = $members()->where('status', MemberStatus::Active)
            ->whereBetween('renews_on', [today(), today()->addDays(30)])->count();
        $overdue = $members()->where('status', MemberStatus::Active)->where('renews_on', '<', today())->count();
        $lapsed = $members()->where('status', MemberStatus::Lapsed)->count();
        $branches = Access::scopeToBranch(Branch::query(), 'id')->count();

        return [
            Stat::make('Active members', $active)
                ->description($joinedThisMonth.' joined this month'),
            Stat::make('Renewals due', $renewing)
                ->description('in the next 30 days'),
            Stat::make('Overdue renewals', $overdue)
                ->description('active, but past their renewal date')
                ->color($overdue ? 'danger' : null)
                ->descriptionColor($overdue ? 'danger' : null),
            Stat::make('Lapsed', $lapsed)
                ->description('didn’t renew'),
            Stat::make('Branches', $branches)
                ->description(Access::branchId() ? 'you look after this branch' : 'in this association'),
        ];
    }

    protected function getColumns(): int
    {
        return 5;
    }
}
