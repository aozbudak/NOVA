<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.system') }}</h2>
    <dl class="flex flex-col gap-3 text-[13px]">
        <div class="flex justify-between gap-3">
            <dt class="text-muted-foreground">{{ __('admin.settings.timezone') }}</dt>
            <dd class="text-foreground">{{ $settings['timezone'] }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt class="text-muted-foreground">{{ __('admin.settings.api_url') }}</dt>
            <dd class="font-mono text-foreground">{{ $settings['api_url'] }}</dd>
        </div>
        <div class="flex justify-between gap-3">
            <dt class="text-muted-foreground">{{ __('admin.settings.api_status') }}</dt>
            <dd class="text-foreground">{{ __('admin.settings.status_'.$settings['api_status']) }}</dd>
        </div>
    </dl>
</section>
