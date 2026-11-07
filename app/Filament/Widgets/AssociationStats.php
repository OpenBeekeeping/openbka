<?php

namespace App\Filament\Widgets;

use App\Enums\MemberStatus;
use App\Models\Branch;
use App\Models\Member;
use App\Support\Access;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssociationStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $members = Access::scopeToBranch(Member::query());

        return [
            Stat::make('Active members', (clone $members)->where('status', MemberStatus::Active)->count()),
            Stat::make('Renewals due in 30 days', (clone $members)->where('status', MemberStatus::Active)
                ->whereBetween('renews_on', [today(), today()->addDays(30)])->count()),
            Stat::make('Branches', Access::scopeToBranch(Branch::query(), 'id')->count()),
        ];
    }
}
