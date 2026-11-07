<x-filament-widgets::widget>
    <section class="ob-card">
        <header><h2>Renewals due</h2><a href="{{ $url }}">All members</a></header>
        @if ($members->isEmpty())
            <div class="ob-empty">Nobody is due to renew in the next 30 days.</div>
        @else
            <div style="overflow-x: auto">
                <table class="ob-table">
                    <thead><tr><th>Member</th><th>Branch</th><th>Renews</th></tr></thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td><a href="{{ \App\Filament\Resources\Members\MemberResource::getUrl('edit', ['record' => $member]) }}">{{ $member->name }}</a></td>
                                <td class="muted">{{ $member->branch->name }}</td>
                                <td>
                                    {{ $member->renews_on->format('j M Y') }}
                                    @if ($member->renews_on->isPast())
                                        <x-filament::badge color="danger">Overdue</x-filament::badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-filament-widgets::widget>
