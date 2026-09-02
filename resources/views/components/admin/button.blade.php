@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $classes = match ($variant) {
        'secondary' => 'border border-border bg-card text-foreground hover:bg-accent',
        'ghost' => 'text-muted-foreground hover:bg-accent hover:text-foreground',
        'danger' => 'bg-destructive text-white hover:opacity-90',
        'icon' => 'size-7 text-muted-foreground hover:bg-accent hover:text-foreground',
        default => 'bg-primary text-primary-foreground hover:opacity-90',
    };

    $base = $variant === 'icon'
        ? 'inline-flex items-center justify-center rounded-xl'
        : 'inline-flex h-9 items-center justify-center gap-1.5 rounded-xl px-3 text-[12px] font-medium';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" size="size-3.5" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>
        @if ($icon)
            <x-icon :name="$icon" size="size-3.5" />
        @endif
        {{ $slot }}
    </button>
@endif
