<?php

namespace App\Filament\Widgets;

use App\Models\Member;
use App\Support\Access;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

/** New members per month for the last 12 months, as plain CSS bars. */
class MembersJoined extends Widget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.members-joined';

    protected int|string|array $columnSpan = 1;

    /** @return array{months: list<array{label: string, count: int}>, max: int, total: int} */
    protected function getViewData(): array
    {
        $start = today()->startOfMonth()->subMonths(11);

        $joined = Access::scopeToBranch(Member::query())
            ->where('joined_on', '>=', $start)
            ->pluck('joined_on')
            ->countBy(fn (Carbon $date): string => $date->format('Y-m'));

        $months = collect(range(0, 11))->map(function (int $i) use ($start, $joined): array {
            $month = $start->copy()->addMonths($i);

            return ['label' => $month->format('M'), 'count' => $joined[$month->format('Y-m')] ?? 0];
        })->all();

        return [
            'months' => $months,
            'max' => max(1, ...array_column($months, 'count')),
            'total' => array_sum(array_column($months, 'count')),
        ];
    }
}
