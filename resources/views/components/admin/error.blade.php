@props([
    'retry' => true,
])

<div {{ $attributes->class('flex flex-col items-center gap-3 px-6 py-12 text-center') }} data-admin-error>
    <span class="flex size-14 items-center justify-center rounded-2xl bg-muted">
        <x-icon name="x" />
    </span>
    <h2 class="font-serif text-2xl text-foreground">{{ __('admin.errors.connection_title') }}</h2>
    <p class="max-w-md text-sm leading-relaxed text-muted-foreground">{{ __('admin.errors.connection_body') }}</p>
    @if ($retry)
        <button type="button" data-admin-retry class="mt-2 inline-flex h-9 items-center rounded-xl bg-primary px-3 text-[12px] font-medium tracking-wide text-primary-foreground uppercase">
            {{ __('admin.errors.retry') }}
        </button>
    @endif
</div>
