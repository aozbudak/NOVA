@if (! empty($report['metrics']))
    <div @class([
        'grid grid-cols-2 gap-3',
        'xl:grid-cols-4' => count($report['metrics']) <= 4,
        'xl:grid-cols-5' => count($report['metrics']) === 5,
        'xl:grid-cols-3' => count($report['metrics']) === 6,
    ])>
        @foreach ($report['metrics'] as $metric)
            <article class="admin-card rounded-2xl border px-4 py-3">
                <p class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ $metric['label'] }}</p>
                <p class="mt-1 font-serif text-2xl tracking-tight text-foreground">{{ $metric['value'] }}</p>
            </article>
        @endforeach
    </div>
@endif
