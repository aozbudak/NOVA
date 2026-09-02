@props(['rows' => 6])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-md border border-border bg-card']) }} aria-hidden="true">
    <div class="flex gap-3 border-b border-border px-3 py-2">
        <div class="h-2.5 w-24 animate-pulse rounded-sm bg-muted"></div>
        <div class="h-2.5 w-20 animate-pulse rounded-sm bg-muted"></div>
        <div class="h-2.5 w-28 animate-pulse rounded-sm bg-muted"></div>
        <div class="h-2.5 w-16 animate-pulse rounded-sm bg-muted"></div>
        <div class="h-2.5 w-20 animate-pulse rounded-sm bg-muted"></div>
    </div>
    <div class="flex flex-col">
        @for ($row = 0; $row < $rows; $row++)
            <div class="flex gap-3 border-b border-border px-3 py-3 last:border-b-0">
                <div class="h-3 w-32 animate-pulse rounded-sm bg-muted"></div>
                <div class="h-3 w-24 animate-pulse rounded-sm bg-muted"></div>
                <div class="h-3 w-20 animate-pulse rounded-sm bg-muted"></div>
                <div class="h-3 w-16 animate-pulse rounded-sm bg-muted"></div>
                <div class="h-3 w-28 animate-pulse rounded-sm bg-muted"></div>
            </div>
        @endfor
    </div>
</div>
