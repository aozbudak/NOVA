<div data-admin-layer="confirm" hidden class="fixed inset-0 z-50">
    <div data-layer-backdrop class="admin-layer-backdrop absolute inset-0"></div>
    <div class="relative flex min-h-full items-start justify-center px-4 py-16">
        <div data-layer-dialog class="admin-popover w-full max-w-sm rounded-2xl border" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <div class="px-5 py-5">
                <h2 id="confirm-title" data-confirm-title class="font-serif text-xl tracking-tight text-foreground"></h2>
                <p data-confirm-body class="mt-2 text-sm leading-relaxed text-muted-foreground"></p>
            </div>
            <form data-confirm-form method="POST" class="flex items-center justify-end gap-2 border-t border-border px-5 py-3">
                @csrf
                <input type="hidden" name="_method" data-confirm-method value="POST">
                <button type="button" data-close-layer class="inline-flex h-9 items-center rounded-xl px-3 text-[12px] text-muted-foreground hover:bg-muted hover:text-foreground">
                    {{ __('admin.common.cancel') }}
                </button>
                <button type="submit" data-confirm-submit data-busy-label="{{ __('admin.common.processing') }}" class="inline-flex h-9 items-center rounded-xl bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                    {{ __('admin.common.confirm') }}
                </button>
            </form>
        </div>
    </div>
</div>
