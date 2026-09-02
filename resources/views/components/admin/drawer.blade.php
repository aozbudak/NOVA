@props([
    'name',
    'title',
])

<div data-admin-layer="{{ $name }}" hidden class="fixed inset-0 z-50">
    <div data-layer-backdrop class="admin-layer-backdrop absolute inset-0"></div>
    <div data-drawer-panel class="admin-popover absolute inset-y-0 right-0 flex w-full max-w-md flex-col rounded-none border-y-0 border-r-0 border-l" role="dialog" aria-modal="true" aria-labelledby="drawer-{{ $name }}-title">
        <div class="flex items-center justify-between gap-3 border-b border-border px-5 py-4">
            <h2 id="drawer-{{ $name }}-title" class="font-serif text-xl tracking-tight text-foreground">{{ $title }}</h2>
            <button type="button" data-close-layer class="rounded-xl p-1 text-muted-foreground hover:bg-muted hover:text-foreground" aria-label="{{ __('admin.common.close') }}">
                <x-icon name="x" size="size-4" />
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-4">{{ $slot }}</div>
        @isset($footer)
            <div class="flex items-center justify-end gap-2 border-t border-border px-5 py-3">{{ $footer }}</div>
        @endisset
    </div>
</div>
