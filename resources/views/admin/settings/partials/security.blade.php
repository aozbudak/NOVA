<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.security') }}</h2>
    <div class="grid gap-4">
        <x-admin.field :label="__('admin.settings.session_timeout')" name="session_timeout">
            <x-admin.input name="session_timeout" type="number" min="5" max="1440" value="{{ old('session_timeout', $settings['session_timeout']) }}" />
        </x-admin.field>
        <x-admin.field :label="__('admin.settings.password_min')" name="password_min">
            <x-admin.input name="password_min" type="number" min="8" max="32" value="{{ old('password_min', $settings['password_min']) }}" />
        </x-admin.field>
        <label class="flex items-center gap-2 text-[13px] text-foreground">
            <input type="checkbox" name="login_protection" value="1" @checked(old('login_protection', $settings['login_protection'])) class="size-3.5 rounded-sm border-input">
            {{ __('admin.settings.login_protection') }}
        </label>
    </div>
</section>
