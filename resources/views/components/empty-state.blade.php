@props(['title', 'action' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-4 px-8 py-20 text-center']) }}>
    <h2 class="font-serif text-3xl">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="max-w-md text-sm text-muted-foreground">{{ $slot }}</p>
    @endif
    @if ($action)
        {{ $action }}
    @endif
</div>
