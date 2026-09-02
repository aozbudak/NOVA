<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.store') }}</h2>
    <div class="grid gap-4">
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.store_info') }}
            <textarea name="store_info" rows="3" class="rounded-md border border-input bg-background px-3 py-2 text-[13px] text-foreground">{{ old('store_info', $settings['store_info']) }}</textarea>
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.opening_hours') }}
            <input name="opening_hours" value="{{ old('opening_hours', $settings['opening_hours']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.default_language') }}
            <select name="default_language" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                <option value="en" @selected(old('default_language', $settings['default_language']) === 'en')>English</option>
                <option value="tr" @selected(old('default_language', $settings['default_language']) === 'tr')>Türkçe</option>
            </select>
        </label>
    </div>
</section>
