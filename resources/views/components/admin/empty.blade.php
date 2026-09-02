@props([
    'title',
    'action' => null,
    'icon' => 'bag',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-3 px-6 py-12 text-center']) }}>
    <span class="flex size-14 items-center justify-center rounded-2xl bg-muted">
        <x-icon :name="$icon" />
    </span>
    <h2 class="font-serif text-2xl text-foreground">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="max-w-md text-sm leading-relaxed text-muted-foreground">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
