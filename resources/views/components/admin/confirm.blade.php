<div data-admin-layer="confirm" hidden class="fixed inset-0 z-50">
    <div data-layer-backdrop class="absolute inset-0 bg-foreground/20"></div>
    <div class="relative flex min-h-full items-start justify-center px-4 py-16">
        <div class="w-full max-w-sm rounded-md border border-border bg-card shadow-sm" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <div class="px-4 py-4">
                <h2 id="confirm-title" data-confirm-title class="text-[13px] font-medium tracking-wide text-foreground uppercase"></h2>
                <p data-confirm-body class="mt-2 text-[13px] text-muted-foreground"></p>
            </div>
            <form data-confirm-form method="POST" class="flex items-center justify-end gap-2 border-t border-border px-4 py-3">
                @csrf
                <input type="hidden" name="_method" data-confirm-method value="POST">
                <button type="button" data-close-layer class="inline-flex h-8 items-center rounded-md px-3 text-[12px] text-muted-foreground hover:text-foreground">
                    {{ __('admin.common.cancel') }}
                </button>
                <button type="submit" data-confirm-submit data-busy-label="{{ __('admin.common.processing') }}" class="inline-flex h-8 items-center rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                    {{ __('admin.common.confirm') }}
                </button>
            </form>
        </div>
    </div>
</div>
