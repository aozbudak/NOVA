@props([
    'paginator',
])

@if ($paginator->total() > 0)
    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[12px] text-muted-foreground">
            {{ __('admin.pagination.showing', [
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
                'total' => $paginator->total(),
            ]) }}
        </p>
        @if ($paginator->hasPages())
            <nav class="flex flex-wrap items-center gap-1" aria-label="{{ __('admin.pagination.label') }}">
                {{ $paginator->onEachSide(2)->links('pagination.admin') }}
            </nav>
        @endif
    </div>
@endif
