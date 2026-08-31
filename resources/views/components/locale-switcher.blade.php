@props([
    'locales' => null,
    'current' => null,
])

@php
    $locales = $locales ?? config('app.available_locales');
    $current = $current ?? app()->getLocale();
@endphp

<nav {{ $attributes->merge(['class' => 'flex items-center gap-2 text-[10px] font-medium tracking-nav uppercase']) }} aria-label="{{ __('storefront.header.language') }}">
    @foreach ($locales as $code => $name)
        @if ($code === $current)
            <span aria-current="true" lang="{{ $code }}" class="opacity-100">{{ strtoupper($code) }}</span>
        @else
            <a
                href="{{ route('locale.update', $code) }}"
                hreflang="{{ $code }}"
                lang="{{ $code }}"
                class="opacity-50 transition-opacity hover:opacity-100"
            >{{ strtoupper($code) }}</a>
        @endif
    @endforeach
</nav>
