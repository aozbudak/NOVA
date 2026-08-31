@props(['title'])

<details {{ $attributes->merge(['class' => 'border-b border-border py-4']) }}>
    <summary class="flex cursor-pointer list-none items-center justify-between text-[11px] tracking-nav uppercase">
        {{ $title }}
        <x-icon name="plus" size="size-3.5" class="open:hidden" />
    </summary>
    <div class="pt-3 text-sm leading-relaxed text-muted-foreground">
        {{ $slot }}
    </div>
</details>
