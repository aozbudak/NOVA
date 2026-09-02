<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.notifications') }}</h2>
    <div class="flex flex-col gap-3">
        <label class="flex items-center gap-2 text-[13px] text-foreground">
            <input type="checkbox" name="notify_low_stock" value="1" @checked(old('notify_low_stock', $settings['notify_low_stock'])) class="size-3.5 rounded-sm border-input">
            {{ __('admin.settings.notify_low_stock') }}
        </label>
        <label class="flex items-center gap-2 text-[13px] text-foreground">
            <input type="checkbox" name="notify_sales" value="1" @checked(old('notify_sales', $settings['notify_sales'])) class="size-3.5 rounded-sm border-input">
            {{ __('admin.settings.notify_sales') }}
        </label>
        <label class="flex items-center gap-2 text-[13px] text-foreground">
            <input type="checkbox" name="notify_returns" value="1" @checked(old('notify_returns', $settings['notify_returns'])) class="size-3.5 rounded-sm border-input">
            {{ __('admin.settings.notify_returns') }}
        </label>
    </div>
</section>
