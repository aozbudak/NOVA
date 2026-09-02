@props([
    'icon',
    'label',
    'href' => null,
])

@php
    $classes = 'inline-flex size-8 items-center justify-center rounded-xl text-muted-foreground transition-colors hover:bg-muted hover:text-foreground';
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
