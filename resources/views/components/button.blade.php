@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
])

@php
    $classes = match ($variant) {
        'outline' => 'border border-foreground bg-transparent text-foreground hover:bg-foreground hover:text-background',
        'ghost' => 'bg-transparent text-foreground hover:text-muted-foreground',
        'light' => 'border border-primary-foreground bg-transparent text-primary-foreground hover:bg-primary-foreground hover:text-primary',
        default => 'border border-primary bg-primary text-primary-foreground hover:opacity-90',
    };

    $base = 'inline-flex items-center justify-center gap-2 px-6 py-3 text-[11px] font-medium tracking-nav uppercase transition-opacity duration-150 disabled:cursor-not-allowed disabled:opacity-40';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>
        {{ $slot }}
    </button>
@endif
