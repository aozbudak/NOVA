@props([
    'items' => [],
])

<div class="relative" data-dropdown="notifications">
    <button
        type="button"
        data-dropdown-trigger
        class="relative inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-accent hover:text-accent-foreground"
        aria-label="{{ __('admin.notifications.title') }}"
        aria-haspopup="menu"
        aria-expanded="false"
    >
        <x-icon name="bell" size="size-4" />
        @if (collect($items)->contains(fn (array $item): bool => $item['unread']))
            <span class="absolute top-1.5 right-1.5 size-1.5 rounded-full bg-destructive"></span>
        @endif
    </button>

    <div data-dropdown-panel hidden class="admin-popover absolute right-0 z-30 mt-2 w-[22rem] overflow-hidden rounded-xl border">
        <div class="flex items-center justify-between border-b border-glass-border px-3.5 py-2.5">
            <p class="text-[13px] font-medium text-foreground">{{ __('admin.notifications.title') }}</p>
            @if (collect($items)->contains(fn (array $item): bool => $item['unread']))
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-[11px] text-muted-foreground hover:text-foreground">{{ __('admin.notifications.mark_all') }}</button>
                </form>
            @endif
        </div>
        <div class="max-h-96 overflow-y-auto">
            @forelse ($items as $item)
                <div @class([
                    'flex gap-3 border-b border-glass-border px-3.5 py-3 last:border-b-0',
                    'bg-accent/50' => $item['unread'],
                ])>
                    <span @class([
                        'mt-1.5 size-1.5 shrink-0 rounded-full',
                        'bg-sidebar-glow' => $item['unread'],
                        'bg-border' => ! $item['unread'],
                    ])></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] text-foreground">{{ $item['title'] }}</p>
                        <p class="truncate text-[12px] text-muted-foreground">{{ $item['body'] }}</p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $item['time'] }}</p>
                    </div>
                    @if ($item['unread'])
                        <form method="POST" action="{{ route('admin.notifications.read', $item['id']) }}">
                            @csrf
                            <button type="submit" class="text-[11px] text-muted-foreground hover:text-foreground">{{ __('admin.notifications.read') }}</button>
                        </form>
                    @else
                        <span class="text-[11px] text-muted-foreground">{{ __('admin.notifications.read_state') }}</span>
                    @endif
                </div>
            @empty
                <x-admin.empty :title="__('admin.empty.notifications.title')" class="py-10">
                    {{ __('admin.empty.notifications.body') }}
                </x-admin.empty>
            @endforelse
        </div>
    </div>
</div>
