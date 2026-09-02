@props([
    'title',
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-2 px-6 py-16 text-center']) }}>
    <h2 class="text-sm font-medium tracking-wide text-foreground uppercase">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <p class="max-w-sm text-[13px] text-muted-foreground">{{ $slot }}</p>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
