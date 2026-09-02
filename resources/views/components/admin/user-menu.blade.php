@props([
    'staff',
])

<div class="relative w-full" data-dropdown="user">
    <button
        type="button"
        data-dropdown-trigger
        class="sidebar-footer flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left transition-colors hover:bg-muted"
        aria-haspopup="menu"
        aria-expanded="false"
    >
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-foreground text-[11px] font-medium tracking-wide text-background uppercase">
            {{ mb_strtoupper(mb_substr($staff->name, 0, 1)) }}
        </span>
        <span class="sidebar-footer-meta min-w-0 flex-1">
            <span class="block truncate text-[13px] leading-tight">{{ $staff->name }}</span>
            <span class="block truncate text-[11px] leading-tight text-muted-foreground">{{ $staff->role->label() }}</span>
        </span>
        <x-icon name="chevron-down" size="size-3.5" class="sidebar-label shrink-0 text-muted-foreground" />
    </button>

    <div data-dropdown-panel hidden class="admin-popover absolute right-0 bottom-full z-30 mb-2 w-60 overflow-hidden rounded-2xl border py-1.5">
        <div class="flex items-center gap-3 border-b border-border px-3 py-2.5">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-foreground text-[12px] font-medium tracking-wide text-background uppercase">
                {{ mb_strtoupper(mb_substr($staff->name, 0, 1)) }}
            </span>
            <span class="min-w-0">
                <p class="truncate text-[13px] font-medium text-foreground">{{ $staff->name }}</p>
                <p class="truncate text-[11px] text-muted-foreground">{{ $staff->role->label() }}</p>
            </span>
        </div>
        <div class="flex flex-col gap-0.5 p-1.5">
            <a href="{{ route('admin.profile.show') }}" class="flex items-center gap-2 rounded-xl px-2.5 py-2 text-[13px] text-foreground transition-colors hover:bg-muted">
                <x-icon name="user" size="size-4" class="text-muted-foreground" />
                {{ __('admin.user_menu.profile') }}
            </a>
            @if ($staff->role->can('settings'))
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 rounded-xl px-2.5 py-2 text-[13px] text-foreground transition-colors hover:bg-muted">
                    <x-icon name="settings" size="size-4" class="text-muted-foreground" />
                    {{ __('admin.user_menu.settings') }}
                </a>
            @endif
        </div>
        <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-border p-1.5">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-2.5 py-2 text-left text-[13px] text-foreground transition-colors hover:bg-muted">
                <x-icon name="logout" size="size-4" class="text-muted-foreground" />
                {{ __('admin.user_menu.logout') }}
            </button>
        </form>
    </div>
</div>
