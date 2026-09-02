@props([
    'action',
    'chips' => [],
    'columns' => 4,
])

<form method="GET" action="{{ $action }}" data-table-filter {{ $attributes->class('mb-4 flex flex-col gap-3') }}>
    <div @class([
        'grid gap-2',
        'md:grid-cols-2' => $columns === 1 || $columns === 2,
        'md:grid-cols-3' => $columns === 3,
        'md:grid-cols-4' => $columns === 4,
        'md:grid-cols-5' => $columns === 5,
        'md:grid-cols-6' => $columns >= 6,
    ])>
        {{ $slot }}
        <div class="flex items-end">
            <x-admin.button type="submit" variant="secondary">{{ __('admin.common.filter') }}</x-admin.button>
        </div>
    </div>
    @if ($chips !== [])
        <div class="flex flex-wrap gap-1.5">
            @foreach ($chips as $chip)
                <a href="{{ $chip['url'] }}" class="inline-flex items-center gap-1 rounded-xl bg-muted px-2.5 py-1 text-[12px] text-foreground hover:bg-accent">
                    <span class="text-muted-foreground">{{ $chip['label'] }}:</span>
                    {{ $chip['value'] }}
                    <x-icon name="x" size="size-3" class="text-muted-foreground" />
                </a>
            @endforeach
        </div>
    @endif
</form>
