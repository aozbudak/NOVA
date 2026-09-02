@props([
    'breadcrumbs' => [],
    'notifications' => [],
])

<header class="sticky top-0 z-20 flex h-[var(--header-height)] items-center gap-3 border-b border-glass-border px-3 glass md:px-4">
    <button
        type="button"
        data-sidebar-toggle
        data-collapse-label="{{ __('admin.sidebar.collapse') }}"
        data-expand-label="{{ __('admin.sidebar.expand') }}"
        class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground"
        aria-label="{{ __('admin.sidebar.collapse') }}"
    >
        <span class="lg:hidden">
            <x-icon name="menu" size="size-4" />
        </span>
        <span class="hidden lg:inline">
            <x-icon name="chevron-double-left" data-sidebar-chevron size="size-4" class="transition-transform duration-200" />
        </span>
    </button>

    <x-admin.breadcrumb :items="$breadcrumbs" />

    <div class="mx-auto hidden min-w-0 max-w-md flex-1 md:block">
        <button
            type="button"
            data-open-search
            class="flex w-full items-center gap-2 rounded-lg border border-glass-border bg-card/70 px-3 py-1.5 text-left text-sm text-muted-foreground hover:border-input"
        >
            <x-icon name="search" size="size-4" />
            <span class="flex-1 truncate">{{ __('admin.search.placeholder') }}</span>
            <kbd class="hidden rounded border border-border px-1.5 py-0.5 text-[10px] text-muted-foreground lg:inline">{{ __('admin.search.shortcut') }}</kbd>
        </button>
    </div>

    <div class="ml-auto flex items-center gap-1">
        <button
            type="button"
            data-open-search
            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground md:hidden"
            aria-label="{{ __('admin.search.placeholder') }}"
        >
            <x-icon name="search" size="size-4" />
        </button>

        <x-admin.notifications :items="$notifications" />

        <button
            type="button"
            data-theme-toggle
            aria-label="{{ __('admin.theme.toggle') }}"
            class="inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground"
        >
            <span class="dark:hidden">
                <x-icon name="moon" size="size-4" />
            </span>
            <span class="hidden dark:inline">
                <x-icon name="sun" size="size-4" />
            </span>
        </button>
    </div>
</header>
