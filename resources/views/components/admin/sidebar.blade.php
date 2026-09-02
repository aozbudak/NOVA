@props([
    'sections' => [],
    'homeRoute' => 'admin.dashboard',
])

<aside
    data-admin-sidebar
    class="fixed inset-y-0 left-0 z-40 flex w-[15.5rem] flex-col border-r border-sidebar-border bg-sidebar transition-[width,transform] duration-200 max-lg:-translate-x-full lg:w-[var(--sidebar-width)]"
>
    <div class="flex h-[var(--header-height)] items-center gap-3 border-b border-sidebar-border px-4">
        <a href="{{ route($homeRoute) }}" class="flex min-w-0 items-baseline gap-2">
            <span class="font-serif text-lg leading-none tracking-tight text-sidebar-foreground">{{ __('admin.brand') }}</span>
            <span class="sidebar-label text-[10px] font-medium tracking-[0.16em] text-sidebar-muted uppercase">{{ __('admin.brand_sub') }}</span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-2 py-4" aria-label="Admin">
        <div class="flex flex-col gap-5">
            @foreach ($sections as $section)
                <div>
                    <p class="sidebar-section px-2.5 pb-2 text-[10px] font-medium tracking-[0.16em] text-sidebar-muted uppercase">{{ $section['label'] }}</p>
                    <ul class="flex flex-col gap-0.5">
                        @foreach ($section['items'] as $item)
                            <li>
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="sidebar-link flex items-center gap-2.5 rounded-md px-2.5 py-2 text-[13px] text-sidebar-foreground transition-colors hover:bg-sidebar-accent {{ request()->routeIs($item['route']) ? 'bg-sidebar-accent font-medium' : '' }}"
                                >
                                    <x-icon :name="$item['icon']" size="size-4" class="shrink-0 text-sidebar-muted" />
                                    <span class="sidebar-label truncate">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </nav>
</aside>

<div
    data-sidebar-backdrop
    class="fixed inset-0 z-30 hidden bg-foreground/20 lg:hidden"
></div>
