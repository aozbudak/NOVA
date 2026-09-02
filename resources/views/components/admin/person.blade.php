@props([
    'name',
    'meta' => null,
])

<div {{ $attributes->class('flex min-w-0 items-center gap-3') }}>
    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-[11px] font-medium text-foreground">
        {{ mb_strtoupper(mb_substr($name, 0, 1)) }}
    </span>
    <span class="min-w-0">
        <span class="block truncate text-[13px] leading-tight text-foreground">{{ $name }}</span>
        @if (filled($meta))
            <span class="mt-0.5 block truncate text-[11px] leading-tight text-muted-foreground">{{ $meta }}</span>
        @endif
    </span>
</div>
