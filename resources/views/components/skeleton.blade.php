@props(['lines' => 2])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }} aria-hidden="true">
    <div class="aspect-[4/5] bg-muted"></div>
    <div class="mt-3 h-3 w-3/4 bg-muted"></div>
    <div class="mt-2 h-3 w-1/3 bg-muted"></div>
</div>
