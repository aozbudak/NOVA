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
                <x-admin.field :label="__('admin.profile.name')" name="name" required>
                    <x-admin.input name="name" value="{{ old('name', $profile['name']) }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.profile.email')" name="email" required>
                    <x-admin.input name="email" type="email" value="{{ old('email', $profile['email']) }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.profile.phone')" name="phone">
                    <x-admin.input name="phone" value="{{ old('phone', $profile['phone']) }}" />
                </x-admin.field>
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
            <x-admin.button class="mt-4" type="submit" data-busy-label="{{ __('admin.common.saving') }}">
                {{ __('admin.common.save') }}
            </x-admin.button>
        </form>

        <form method="POST" action="{{ route('admin.profile.password') }}" class="rounded-md border border-border bg-card p-4">
            @csrf
            @method('PUT')
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.profile.password') }}</h2>
            <div class="grid gap-4">
                <x-admin.field :label="__('admin.profile.current_password')" name="current_password" required>
                    <x-admin.input name="current_password" type="password" autocomplete="current-password" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.profile.new_password')" name="password" required>
                    <x-admin.input name="password" type="password" autocomplete="new-password" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.profile.password_confirmation')" name="password_confirmation" required>
                    <x-admin.input name="password_confirmation" type="password" autocomplete="new-password" required />
                </x-admin.field>
            </div>
            <x-admin.button class="mt-4" type="submit" data-busy-label="{{ __('admin.common.saving') }}">
                {{ __('admin.common.save') }}
            </x-admin.button>
        </form>
    </div>
@endsection
