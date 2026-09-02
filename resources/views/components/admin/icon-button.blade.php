@props([
    'icon',
    'label',
    'href' => null,
])

@php
    $classes = 'inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'aria-label' => $label]) }}>
        <x-icon :name="$icon" size="size-3.5" />
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes, 'aria-label' => $label]) }}>
        <x-icon :name="$icon" size="size-3.5" />
    </button>
@endif
