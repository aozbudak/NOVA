@props([
    'staff',
])

<div class="relative" data-dropdown="user">
    <button
        type="button"
        data-dropdown-trigger
        class="flex max-w-48 items-center gap-2 rounded-md px-2 py-1 hover:bg-accent"
        aria-haspopup="menu"
        aria-expanded="false"
    >
        <span class="flex size-7 shrink-0 items-center justify-center rounded-md bg-muted text-[11px] font-medium text-foreground">
            {{ mb_strtoupper(mb_substr($staff->name, 0, 1)) }}
        </span>
        <span class="hidden min-w-0 text-left lg:block">
            <span class="block truncate text-[13px] leading-tight text-foreground">{{ $staff->name }}</span>
            <span class="block truncate text-[11px] leading-tight text-muted-foreground">{{ $staff->role->label() }}</span>
        </span>
        <x-icon name="chevron-down" size="size-3.5" class="hidden text-muted-foreground lg:block" />
    </button>

    <div data-dropdown-panel hidden class="absolute right-0 z-30 mt-2 w-52 rounded-md border border-border bg-card py-1 shadow-sm">
        <div class="border-b border-border px-3 py-2 lg:hidden">
            <p class="text-[13px] text-foreground">{{ $staff->name }}</p>
            <p class="text-[11px] text-muted-foreground">{{ $staff->role->label() }}</p>
        </div>
        <a href="{{ route('admin.profile.show') }}" class="flex items-center gap-2 px-3 py-2 text-[13px] text-foreground hover:bg-accent">
            <x-icon name="user" size="size-4" class="text-muted-foreground" />
            {{ __('admin.user_menu.profile') }}
        </a>
        @if ($staff->role->can('settings'))
            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-3 py-2 text-[13px] text-foreground hover:bg-accent">
                <x-icon name="settings" size="size-4" class="text-muted-foreground" />
                {{ __('admin.user_menu.settings') }}
            </a>
        @endif
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-2 px-3 py-2 text-left text-[13px] text-foreground hover:bg-accent">
                <x-icon name="logout" size="size-4" class="text-muted-foreground" />
                {{ __('admin.user_menu.logout') }}
            </button>
        </form>
    </div>
</div>
