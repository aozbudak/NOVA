@extends('layouts.admin-guest')

@section('title', __('admin.auth.title'))

@section('content')
    <div class="rounded-md border border-border bg-card p-8">
        <p class="text-center text-[11px] font-medium tracking-[0.32em] uppercase">{{ __('admin.brand') }}</p>
        <h1 class="mt-3 text-center text-sm font-medium tracking-wide text-foreground uppercase">{{ __('admin.auth.title') }}</h1>

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
@endsection
