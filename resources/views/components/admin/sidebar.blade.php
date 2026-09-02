@props([
    'sections' => [],
    'homeRoute' => 'admin.dashboard',
    'staff' => null,
])

<aside
    data-admin-sidebar
    class="fixed inset-y-0 left-0 z-40 flex w-[16rem] flex-col border-r border-sidebar-border text-sidebar-foreground transition-[width,transform] duration-200 max-lg:-translate-x-full lg:w-[var(--sidebar-width)]"
>
    <div class="sidebar-brand flex h-[var(--header-height)] items-center gap-3 border-b border-sidebar-border px-4">
        <a href="{{ route($homeRoute) }}" class="flex min-w-0 items-center gap-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white/10 font-serif text-sm text-sidebar-foreground ring-1 ring-white/15">
                {{ mb_substr(__('admin.brand'), 0, 1) }}
            </span>
            <span class="sidebar-brand-text min-w-0">
                <span class="block font-serif text-lg leading-none tracking-tight">{{ __('admin.brand') }}</span>
                <span class="sidebar-label mt-1 block text-[10px] font-medium tracking-[0.16em] text-sidebar-muted uppercase">{{ __('admin.brand_sub') }}</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-2.5 py-4" aria-label="Admin">
        <div class="flex flex-col gap-1">
            @foreach ($sections as $section)
                @php
                    $isOpen = collect($section['items'])->contains(fn (array $item): bool => ! empty($item['active']));
                    $groupId = 'nav-group-'.$section['key'];
                @endphp
                <div
                    data-nav-group="{{ $section['key'] }}"
                    @if ($isOpen) data-open data-active-group="true" @endif
                    class="group/nav"
                >
                    <button
                        type="button"
                        data-nav-group-toggle
                        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                        aria-controls="{{ $groupId }}"
                        class="sidebar-section flex w-full cursor-pointer items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-left text-[10px] font-medium tracking-[0.16em] text-sidebar-muted uppercase transition-colors duration-150 hover:bg-sidebar-accent hover:text-sidebar-foreground"
                    >
                        <span class="sidebar-label truncate">{{ $section['label'] }}</span>
                        <x-icon name="chevron-down" size="size-3.5" class="sidebar-group-chevron shrink-0 transition-transform duration-200" />
                    </button>
                    <ul
                        id="{{ $groupId }}"
                        data-nav-group-items
                        @class([
                            'flex-col gap-0.5',
                            'flex' => $isOpen,
                            'hidden' => ! $isOpen,
                        ])
                    >
                        @foreach ($section['items'] as $item)
                            <li>
                                <a
                                    href="{{ route($item['route'], $item['parameters'] ?? []) }}"
                                    title="{{ $item['label'] }}"
                                    @if (! empty($item['active'])) data-active="true" @endif
                                    class="sidebar-link flex items-center gap-2.5 rounded-lg px-2 py-1.5 text-[13px] text-sidebar-foreground/90 transition-colors duration-150 {{ ! empty($item['active']) ? 'font-medium text-sidebar-foreground' : '' }}"
                                >
                                    <span class="sidebar-icon flex size-7 shrink-0 items-center justify-center rounded-md text-sidebar-muted">
                                        <x-icon :name="$item['icon']" size="size-4" />
                                    </span>
                                    <span class="sidebar-label truncate">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </nav>

    @if ($staff)
        <div class="border-t border-sidebar-border px-2 py-2">
            <x-locale-switcher class="sidebar-label mb-1 px-2 text-sidebar-muted" />
            <x-admin.user-menu :staff="$staff" />
        </div>
    @endif
</aside>

<div
    data-sidebar-backdrop
    class="fixed inset-0 z-30 hidden bg-foreground/40 backdrop-blur-sm lg:hidden"
></div>
