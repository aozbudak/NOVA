@extends('layouts.admin')

@section('title', __('admin.profile.title'))

@section('content')
    <x-admin.page-header :title="__('admin.profile.title')" />

    <div class="grid max-w-3xl gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.profile.update') }}" class="rounded-md border border-border bg-card p-4">
            @csrf
            @method('PUT')
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.profile.info') }}</h2>
            <div class="grid gap-4">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.profile.name') }}
                    <input name="name" value="{{ old('name', $profile['name']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.profile.email') }}
                    <input name="email" type="email" value="{{ old('email', $profile['email']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.profile.phone') }}
                    <input name="phone" value="{{ old('phone', $profile['phone']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <div class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.profile.role') }}
                    <p class="h-9 rounded-md border border-border bg-muted/40 px-3 text-[13px] leading-9 text-foreground">{{ $profile['role'] }}</p>
                </div>
            </div>
            @if ($profile['abilities'] !== [])
                <div class="mt-4">
                    <p class="mb-2 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.users.abilities') }}</p>
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach ($profile['abilities'] as $ability)
                            <li class="rounded-sm bg-muted px-1.5 py-0.5 text-[11px] text-foreground">{{ __('admin.nav.'.$ability) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <button type="submit" data-busy-label="{{ __('admin.common.saving') }}" class="mt-4 inline-flex h-9 items-center rounded-md bg-primary px-4 text-[12px] font-medium text-primary-foreground">
                {{ __('admin.common.save') }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="rounded-md border border-border bg-card p-4">
            @csrf
            @method('PUT')
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.profile.password') }}</h2>
            <div class="grid gap-4">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.profile.current_password') }}
                    <input name="current_password" type="password" autocomplete="current-password" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.profile.new_password') }}
                    <input name="password" type="password" autocomplete="new-password" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.profile.password_confirmation') }}
                    <input name="password_confirmation" type="password" autocomplete="new-password" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
            </div>
            <button type="submit" data-busy-label="{{ __('admin.common.saving') }}" class="mt-4 inline-flex h-9 items-center rounded-md bg-primary px-4 text-[12px] font-medium text-primary-foreground">
                {{ __('admin.common.save') }}
            </button>
        </form>
    </div>
@endsection
