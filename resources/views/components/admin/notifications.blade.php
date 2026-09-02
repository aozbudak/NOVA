@props([
    'items' => [],
])

<div class="relative" data-dropdown="notifications">
    <button
        type="button"
        data-dropdown-trigger
        class="relative inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground"
        aria-label="{{ __('admin.notifications.title') }}"
        aria-haspopup="menu"
        aria-expanded="false"
    >
        <x-icon name="bell" size="size-4" />
        @if (collect($items)->contains(fn (array $item): bool => $item['unread']))
            <span class="absolute top-1.5 right-1.5 size-1.5 rounded-full bg-destructive"></span>
        @endif
    </button>

    <div data-dropdown-panel hidden class="absolute right-0 z-30 mt-2 w-80 rounded-md border border-border bg-card shadow-sm">
        <div class="flex items-center justify-between border-b border-border px-3 py-2">
            <p class="text-[13px] font-medium text-foreground">{{ __('admin.notifications.title') }}</p>
        </div>
        @forelse ($items as $item)
            <div class="flex gap-3 border-b border-border px-3 py-2.5 last:border-b-0">
                <span @class([
                    'mt-1.5 size-1.5 shrink-0 rounded-full',
                    'bg-primary' => $item['unread'],
                    'bg-border' => ! $item['unread'],
                ])></span>
                <div class="min-w-0">
                    <p class="text-[13px] text-foreground">{{ $item['title'] }}</p>
                    <p class="truncate text-[12px] text-muted-foreground">{{ $item['body'] }}</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $item['time'] }}</p>
                </div>
            </div>
        @empty
            <p class="px-3 py-6 text-center text-[13px] text-muted-foreground">{{ __('admin.notifications.empty') }}</p>
        @endforelse
    </div>
</div>
