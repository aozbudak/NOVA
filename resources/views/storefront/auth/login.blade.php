@extends('layouts.storefront')

@section('title', __('storefront.auth.welcome_back'))

@section('content')
    <div class="relative overflow-hidden px-4 py-16 md:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute top-8 left-[12%] size-72 rounded-full bg-foreground/8 blur-3xl"></div>
            <div class="absolute right-[10%] bottom-12 size-80 rounded-full bg-foreground/6 blur-3xl"></div>
        </div>
        <div class="relative mx-auto max-w-md border glass-panel px-8 py-12 md:px-10 md:py-14">
            <h1 class="text-center font-serif text-4xl">{{ __('storefront.auth.welcome_back') }}</h1>
            <form method="post" action="{{ route('login.store') }}" class="mt-12 flex flex-col gap-8">
                @csrf
                <x-input name="email" :label="__('storefront.auth.email')" type="email" :required="true" autocomplete="email" />
                <x-input name="password" :label="__('storefront.auth.password')" type="password" :required="true" autocomplete="current-password" />
                <a href="{{ route('pages.show', 'contact') }}" class="text-[11px] tracking-label uppercase text-muted-foreground">{{ __('storefront.auth.forgot') }}</a>
                <x-button type="submit" class="w-full">{{ __('storefront.auth.login') }}</x-button>
            </form>
            <p class="mt-10 text-center text-sm text-muted-foreground">
                {{ __('storefront.auth.no_account') }}
                <a href="{{ route('register') }}" class="ml-1 text-foreground tracking-label uppercase text-[11px]">{{ __('storefront.auth.create_account') }}</a>
            </p>
        </div>
    </div>
@endsection
