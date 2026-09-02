@props(['count' => 4])

<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-3 lg:grid-cols-4']) }} aria-hidden="true">
    @for ($i = 0; $i < $count; $i++)
        <article class="rounded-md border border-border bg-card px-4 py-3">
            <div class="h-2.5 w-20 animate-pulse rounded-sm bg-muted"></div>
            <div class="mt-3 h-7 w-24 animate-pulse rounded-sm bg-muted"></div>
        </article>
    @endfor
</div>
