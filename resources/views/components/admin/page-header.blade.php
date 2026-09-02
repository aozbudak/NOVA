@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-5 flex flex-wrap items-start justify-between gap-3']) }}>
    <div class="flex min-w-0 flex-col gap-1">
        <h1 class="font-serif text-3xl tracking-tight text-foreground">{{ $title }}</h1>
        @if ($description)
            <p class="max-w-xl text-sm leading-relaxed text-muted-foreground">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
