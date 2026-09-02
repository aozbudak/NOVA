@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-1']) }}>
    <h1 class="font-serif text-2xl tracking-tight text-foreground">{{ $title }}</h1>
    @if ($description)
        <p class="text-sm text-muted-foreground">{{ $description }}</p>
    @endif
</div>
