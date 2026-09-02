@props([
    'name',
    'title',
    'wide' => false,
])

<div data-admin-layer="{{ $name }}" hidden class="fixed inset-0 z-50">
    <div data-layer-backdrop class="admin-layer-backdrop absolute inset-0"></div>
    <div class="relative flex min-h-full items-center justify-center overflow-y-auto px-4 py-6">
        <div
            data-layer-dialog
            @class([
                'admin-popover w-full rounded-xl border',
                'max-w-lg' => ! $wide,
                'max-w-2xl' => $wide,
            ])
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-{{ $name }}-title"
        >
            <div class="flex items-center justify-between gap-3 border-b border-glass-border px-4 py-3">
                <h2 id="modal-{{ $name }}-title" class="text-[13px] font-medium text-foreground">{{ $title }}</h2>
                <button type="button" data-close-layer class="rounded-md p-1 text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.close') }}">
                    <x-icon name="x" size="size-4" />
                </button>
            </div>
            <div class="px-4 py-4">{{ $slot }}</div>
            @isset($footer)
                <div class="flex items-center justify-end gap-2 border-t border-glass-border px-4 py-3">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
