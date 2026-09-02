<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.general') }}</h2>
    <div class="grid gap-4">
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.store_name') }}
            <input name="store_name" value="{{ old('store_name', $settings['store_name']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.store_email') }}
            <input name="store_email" type="email" value="{{ old('store_email', $settings['store_email']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.phone') }}
            <input name="phone" value="{{ old('phone', $settings['phone']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.address') }}
            <input name="address" value="{{ old('address', $settings['address']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.currency') }}
            <input name="currency" value="{{ old('currency', $settings['currency']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
    </div>
</section>
