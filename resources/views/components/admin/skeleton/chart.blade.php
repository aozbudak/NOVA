<div {{ $attributes->merge(['class' => 'admin-card rounded-2xl border p-4']) }} aria-hidden="true">
    <div class="h-3 w-32 animate-pulse rounded-sm bg-muted"></div>
    <div class="mt-4 flex h-32 items-end gap-2">
        @foreach ([40, 65, 50, 80, 45, 70, 55, 90] as $height)
            <div class="flex-1 animate-pulse rounded-sm bg-muted" style="height: {{ $height }}%"></div>
        @endforeach
    </div>
</div>
