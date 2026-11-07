<x-filament-widgets::widget>
    <section class="ob-card">
        <header><h2>New members per month</h2><span class="ob-hint">{{ $total }} in the last year</span></header>
        <div class="ob-body">
            <div class="ob-bars" style="--n: 12" role="img"
                 aria-label="New members per month for the last 12 months: {{ collect($months)->map(fn ($m) => $m['label'].' '.$m['count'])->join(', ') }}">
                @foreach ($months as $month)
                    <div class="ob-bar">
                        <b>{{ $month['count'] ?: '' }}</b>
                        <i style="height: {{ round($month['count'] / $max * 85) }}%"></i>
                        <span>{{ $month['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-filament-widgets::widget>
