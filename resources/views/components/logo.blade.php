@props([
    'compact' => false,
])

@php
    $src = $compact ? asset('images/logo-mark.png') : asset('images/logo.png');
    $height = $compact ? 'h-8 w-auto md:h-9' : 'h-[4.5rem] w-auto md:h-20';
@endphp

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => $compact ? 'inline-flex items-center gap-2.5 text-current' : 'inline-flex items-center']) }}>
    <img
        src="{{ $src }}"
        alt="{{ $compact ? '' : 'NOVA' }}"
        class="logo-img {{ $height }}"
        @if ($compact) width="36" height="36" @else width="200" height="110" @endif
    >
    @if ($compact)
        <span class="flex flex-col leading-none">
            <span class="text-[12px] font-semibold tracking-[0.32em]">NOVA</span>
            <span class="mt-1 text-[7px] font-normal tracking-[0.42em] uppercase opacity-70">Clothing</span>
        </span>
    @endif
</a>
