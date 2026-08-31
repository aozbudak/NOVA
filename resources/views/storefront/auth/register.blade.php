@extends('layouts.storefront')

@section('title', __('storefront.auth.create_account'))

@section('content')
    <div class="mx-auto max-w-md px-4 py-20">
        <h1 class="text-center font-serif text-4xl">{{ __('storefront.auth.create_account') }}</h1>
        <form method="post" action="{{ route('register.store') }}" class="mt-12 flex flex-col gap-8">
            @csrf
            <x-input name="first_name" :label="__('storefront.auth.first_name')" :required="true" autocomplete="given-name" />
            <x-input name="last_name" :label="__('storefront.auth.last_name')" :required="true" autocomplete="family-name" />
            <x-input name="email" :label="__('storefront.auth.email')" type="email" :required="true" autocomplete="email" />
            <x-input name="password" :label="__('storefront.auth.password')" type="password" :required="true" autocomplete="new-password" />
            <x-input name="password_confirmation" :label="__('storefront.auth.confirm_password')" type="password" :required="true" autocomplete="new-password" />
            <x-button type="submit" class="w-full">{{ __('storefront.auth.create_account') }}</x-button>
        </form>
        <p class="mt-10 text-center text-sm text-muted-foreground">
            {{ __('storefront.auth.has_account') }}
            <a href="{{ route('login') }}" class="ml-1 text-[11px] tracking-label text-foreground uppercase">{{ __('storefront.auth.login') }}</a>
        </p>
    </div>
@endsection
