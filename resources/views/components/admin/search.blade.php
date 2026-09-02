@props([
    'sections' => [],
])

<div data-admin-search hidden class="fixed inset-0 z-50" data-search-url="{{ route('api.admin.search') }}" data-search-groups='@json(__("admin.search.groups"))'>
    <div data-search-backdrop class="absolute inset-0 bg-foreground/20"></div>
    <div class="relative mx-auto mt-[12vh] w-full max-w-lg px-4">
        <div class="overflow-hidden rounded-md border border-border bg-card shadow-sm">
            <div class="flex items-center gap-2 border-b border-border px-3">
                <x-icon name="search" size="size-4" class="text-muted-foreground" />
                <input
                    type="search"
                    data-admin-search-input
                    placeholder="{{ __('admin.search.placeholder') }}"
                    class="h-11 w-full bg-transparent text-sm text-foreground outline-none placeholder:text-muted-foreground"
                    autocomplete="off"
                >
                <button type="button" data-close-search class="text-muted-foreground hover:text-foreground">
                    <x-icon name="x" size="size-4" />
                </button>
            </div>
            <div data-admin-search-pages class="max-h-80 overflow-y-auto py-1">
                @foreach ($sections as $section)
                    @foreach ($section['items'] as $item)
                        <a
                            href="{{ route($item['route'], $item['parameters'] ?? []) }}"
                            data-search-item
                            data-search-label="{{ $item['label'] }}"
                            class="flex items-center gap-2 px-3 py-2 text-[13px] text-foreground hover:bg-accent"
                        >
                            <x-icon :name="$item['icon']" size="size-4" class="text-muted-foreground" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                @endforeach
            </div>
            <div data-admin-search-groups hidden class="max-h-80 overflow-y-auto py-1"></div>
            <p data-admin-search-empty hidden class="px-3 py-6 text-center text-[13px] text-muted-foreground">{{ __('admin.search.empty') }}</p>
        </div>
    </div>
</div>
