<x-filament-widgets::widget>
    <section class="ob-card">
        <header><h2>Active members by branch</h2><a href="{{ $url }}">All branches</a></header>
        <div class="ob-body">
            @if ($branches->isEmpty())
                <p class="ob-hint">No branches yet.</p>
            @else
                <ol class="ob-rank">
                    @foreach ($branches as $branch)
                        <li><span>{{ $branch->name }}</span><span>{{ $branch->members_count }}</span></li>
                    @endforeach
                </ol>
            @endif
        </div>
    </section>
</x-filament-widgets::widget>
