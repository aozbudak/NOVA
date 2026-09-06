<div
    id="admin-toast"
    class="pointer-events-none fixed inset-x-0 bottom-6 z-50 flex justify-center px-4 opacity-0 transition-opacity duration-200"
    role="status"
    aria-live="polite"
    hidden
    data-flash-status="{{ session('status') }}"
    data-flash-error="{{ session('error') }}"
    data-error-fallback="{{ __('admin.toast.error') }} {{ __('admin.toast.retry') }}"
>
    <p data-toast-message class="whitespace-pre-line rounded-2xl bg-foreground px-4 py-2 text-[11px] tracking-nav text-background uppercase"></p>
</div>
