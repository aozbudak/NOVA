@extends('layouts.admin-guest')

@section('title', __('admin.auth.title'))

@section('content')
    <div class="login-glass relative overflow-hidden rounded-xl border px-8 py-10">
        <div class="login-shine pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="relative">
            <div class="flex flex-col items-center gap-3">
                <span class="flex size-11 items-center justify-center rounded-xl bg-primary/90 font-serif text-lg text-primary-foreground shadow-[0_0_24px_color-mix(in_oklab,var(--sidebar-glow)_35%,transparent)]">
                    {{ mb_substr(__('admin.brand'), 0, 1) }}
                </span>
                <p class="text-[11px] font-medium tracking-[0.32em] uppercase">{{ __('admin.brand') }}</p>
            </div>
            <h1 class="mt-4 text-center font-serif text-2xl tracking-tight text-foreground">{{ __('admin.auth.title') }}</h1>
            <x-locale-switcher class="mt-3 justify-center text-muted-foreground" />

            <form method="post" action="{{ route('admin.login.store') }}" class="mt-8 flex flex-col gap-4">
                @csrf
                <x-admin.field :label="__('admin.auth.username')" name="username" required>
                    <x-admin.input name="username" value="{{ old('username') }}" autocomplete="username" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.auth.password')" name="password" required>
                    <x-admin.input name="password" type="password" autocomplete="current-password" required />
                </x-admin.field>
                <x-admin.button type="submit" class="mt-2 w-full">{{ __('admin.auth.submit') }}</x-admin.button>
            </form>
        </div>
    </div>
@endsection
