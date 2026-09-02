@extends('layouts.storefront')

@section('title', __('storefront.account.profile'))

@section('content')
    <x-account.shell>
        @php
            $first = (string) ($customer['first_name'] ?? '');
            $last = (string) ($customer['last_name'] ?? '');
            $fullName = trim($first.' '.$last);
            $initials = mb_strtoupper(mb_substr($first, 0, 1).mb_substr($last, 0, 1));
        @endphp

        <x-account.card class="overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-4">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-foreground text-sm font-medium tracking-wide text-background uppercase">
                    {{ $initials !== '' ? $initials : 'N' }}
                </span>
                <div class="min-w-0">
                    <h1 class="truncate font-serif text-2xl tracking-tight">{{ $fullName }}</h1>
                    <p class="truncate text-sm text-muted-foreground">{{ $customer['email'] }}</p>
                </div>
            </div>
        </x-account.card>

        <div class="grid items-start gap-4 xl:grid-cols-2">
            <form method="POST" action="{{ route('account.profile.update') }}" class="account-card overflow-hidden rounded-2xl border">
                @csrf
                @method('PUT')
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-sm font-medium">{{ __('storefront.account.info') }}</h2>
                    <p class="mt-1 text-[12px] text-muted-foreground">{{ __('storefront.account.info_hint') }}</p>
                </div>
                <div class="grid gap-6 p-5 sm:grid-cols-2">
                    <x-input name="first_name" :label="__('storefront.auth.first_name')" :value="old('first_name', $customer['first_name'] ?? '')" :required="true" autocomplete="given-name" />
                    <x-input name="last_name" :label="__('storefront.auth.last_name')" :value="old('last_name', $customer['last_name'] ?? '')" :required="true" autocomplete="family-name" />
                    <x-input name="email" :label="__('storefront.account.email')" type="email" :value="old('email', $customer['email'] ?? '')" :required="true" autocomplete="email" />
                    <x-input name="phone" :label="__('storefront.account.phone')" type="tel" :value="old('phone', $customer['phone'] ?? '')" autocomplete="tel" />
                </div>
                <div class="border-t border-border px-5 py-3">
                    <x-button type="submit">{{ __('storefront.account.save') }}</x-button>
                </div>
            </form>

            <form method="POST" action="{{ route('account.password') }}" class="account-card overflow-hidden rounded-2xl border">
                @csrf
                @method('PUT')
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-sm font-medium">{{ __('storefront.account.password') }}</h2>
                    <p class="mt-1 text-[12px] text-muted-foreground">{{ __('storefront.account.password_hint') }}</p>
                </div>
                <div class="flex flex-col gap-6 p-5">
                    <x-input name="current_password" :label="__('storefront.account.current_password')" type="password" :required="true" autocomplete="current-password" />
                    <x-input name="password" :label="__('storefront.account.new_password')" type="password" :required="true" autocomplete="new-password" />
                    <x-input name="password_confirmation" :label="__('storefront.account.password_confirmation')" type="password" :required="true" autocomplete="new-password" />
                </div>
                <div class="border-t border-border px-5 py-3">
                    <x-button type="submit">{{ __('storefront.account.save') }}</x-button>
                </div>
            </form>
        </div>
    </x-account.shell>
@endsection
