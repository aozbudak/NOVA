@props([
    'retry' => true,
])

<div {{ $attributes->class('flex flex-col items-center gap-2 px-6 py-16 text-center') }} data-admin-error>
    <h2 class="text-sm font-medium tracking-wide text-foreground uppercase">{{ __('admin.errors.connection_title') }}</h2>
    <p class="max-w-sm text-[13px] text-muted-foreground">{{ __('admin.errors.connection_body') }}</p>
    @if ($retry)
        <button type="button" data-admin-retry class="mt-2 inline-flex h-8 items-center rounded-md bg-primary px-3 text-[12px] font-medium tracking-wide text-primary-foreground uppercase">
            {{ __('admin.errors.retry') }}
        </button>
    @endif
</div>
