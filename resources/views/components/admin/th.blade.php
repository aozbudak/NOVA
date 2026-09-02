@props([
    'sort' => null,
    'align' => 'start',
])

@php
    $current = request()->string('sort')->toString();
    $direction = request()->string('dir')->toString() === 'desc' ? 'desc' : 'asc';
    $active = $sort !== null && $current === $sort;
    $next = $active && $direction === 'asc' ? 'desc' : 'asc';
    $href = $sort === null ? null : request()->fullUrlWithQuery(['sort' => $sort, 'dir' => $next]);
@endphp

<th
    {{ $attributes->class([
        'px-3 py-2 font-medium',
        'text-right' => $align === 'end',
        'text-center' => $align === 'center',
    ]) }}
>
    @if ($href)
        <a href="{{ $href }}" class="inline-flex items-center gap-1 hover:text-foreground">
            {{ $slot }}
            <x-icon name="chevron-down" size="size-3" @class(['opacity-40' => ! $active, 'rotate-180' => $active && $direction === 'asc']) />
        </a>
    @else
        {{ $slot }}
    @endif
</th>
