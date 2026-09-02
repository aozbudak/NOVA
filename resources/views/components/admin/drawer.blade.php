@props([
    'name',
    'title',
])

<div data-admin-layer="{{ $name }}" hidden class="fixed inset-0 z-50">
    <div data-layer-backdrop class="absolute inset-0 bg-foreground/20"></div>
    <div class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-border bg-card shadow-sm" role="dialog" aria-modal="true" aria-labelledby="drawer-{{ $name }}-title">
        <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
            <h2 id="drawer-{{ $name }}-title" class="text-[13px] font-medium text-foreground">{{ $title }}</h2>
            <button type="button" data-close-layer class="text-muted-foreground hover:text-foreground" aria-label="{{ __('admin.common.close') }}">
                <x-icon name="x" size="size-4" />
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-4">{{ $slot }}</div>
        @isset($footer)
            <div class="flex items-center justify-end gap-2 border-t border-border px-4 py-3">{{ $footer }}</div>
        @endisset
    </div>
</div>
