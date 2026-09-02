@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1']) }}>
    <p class="text-[11px] font-medium tracking-label uppercase text-muted-foreground">{{ $label }}</p>
    <p class="font-serif text-2xl tracking-tight md:text-[1.75rem]">{{ $value }}</p>
</div>
