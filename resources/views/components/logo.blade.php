@props([
    'size' => 'text-[13px]',
])

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'font-sans '.$size.' font-medium tracking-[0.38em] text-current']) }}>
    NOVA
</a>
