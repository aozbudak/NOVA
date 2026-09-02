@extends('layouts.admin')

@section('title', __('admin.profile.title'))

@section('content')
    <x-admin.page-header :title="__('admin.profile.title')" />

    <div class="flex flex-col gap-6">
        <x-admin.card class="overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-4">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-foreground text-sm font-medium tracking-wide text-background uppercase">
                    {{ mb_strtoupper(mb_substr($profile['name'], 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="truncate font-serif text-2xl tracking-tight text-foreground">{{ $profile['name'] }}</h2>
                        <span class="rounded-xl bg-muted px-2 py-0.5 text-[10px] font-medium tracking-nav text-muted-foreground uppercase">{{ $profile['role'] }}</span>
                    </div>
                    <p class="mt-0.5 truncate text-sm text-muted-foreground">{{ $profile['email'] }}</p>
                    @if (filled($profile['phone']))
                        <p class="truncate text-sm text-muted-foreground">{{ $profile['phone'] }}</p>
                    @endif
                </div>
            </div>
            @if ($profile['abilities'] !== [])
                <div class="flex flex-col gap-2 border-t border-border px-5 py-4">
                    <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.users.abilities') }}</p>
                    <ul class="flex flex-wrap gap-1.5">
                        @foreach ($profile['abilities'] as $ability)
                            <li class="rounded-sm bg-muted px-2 py-1 text-[11px] text-foreground">{{ __('admin.nav.'.$ability) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-admin.card>

        <div class="grid items-stretch gap-6 xl:grid-cols-2">
            <form method="POST" action="{{ route('admin.profile.update') }}" class="flex flex-col admin-card rounded-2xl border">
                @csrf
                @method('PUT')
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-sm font-medium text-foreground">{{ __('admin.profile.info') }}</h2>
                    <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.profile.info_hint') }}</p>
                </div>
                <div class="grid flex-1 gap-4 p-5 sm:grid-cols-2">
                    <x-admin.field :label="__('admin.profile.name')" name="name" required>
                        <x-admin.input name="name" value="{{ old('name', $profile['name']) }}" required />
                    </x-admin.field>
                    <x-admin.field :label="__('admin.profile.email')" name="email" required>
                        <x-admin.input name="email" type="email" value="{{ old('email', $profile['email']) }}" required />
                    </x-admin.field>
                    <x-admin.field :label="__('admin.profile.phone')" name="phone">
                        <x-admin.input name="phone" value="{{ old('phone', $profile['phone']) }}" />
                    </x-admin.field>
                    <x-admin.field :label="__('admin.profile.role')">
                        <p class="flex h-9 items-center rounded-xl border border-border bg-muted/40 px-3 text-[13px] text-foreground">{{ $profile['role'] }}</p>
                    </x-admin.field>
                </div>
                <div class="border-t border-border px-5 py-3">
                    <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">
                        {{ __('admin.common.save') }}
                    </x-admin.button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.profile.password') }}" class="flex flex-col admin-card rounded-2xl border">
                @csrf
                @method('PUT')
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-sm font-medium text-foreground">{{ __('admin.profile.password') }}</h2>
                    <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.profile.password_hint') }}</p>
                </div>
                <div class="flex flex-1 flex-col gap-4 p-5">
                    <x-admin.field :label="__('admin.profile.current_password')" name="current_password" required>
                        <x-admin.input name="current_password" type="password" autocomplete="current-password" required />
                    </x-admin.field>
                    <x-admin.field :label="__('admin.profile.new_password')" name="password" required :help="__('admin.users.password_help')">
                        <x-admin.input name="password" type="password" autocomplete="new-password" required />
                    </x-admin.field>
                    <x-admin.field :label="__('admin.profile.password_confirmation')" name="password_confirmation" required>
                        <x-admin.input name="password_confirmation" type="password" autocomplete="new-password" required />
                    </x-admin.field>
                </div>
                <div class="border-t border-border px-5 py-3">
                    <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">
                        {{ __('admin.common.save') }}
                    </x-admin.button>
                </div>
            </form>
        </div>
    </div>
@endsection
