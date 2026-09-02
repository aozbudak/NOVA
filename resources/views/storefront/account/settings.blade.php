@extends('layouts.storefront')

@section('title', __('storefront.account.settings'))

@section('content')
    <x-account.shell>
        <x-account.card class="divide-y divide-border overflow-hidden">
            <div class="px-5 py-4">
                <h1 class="font-serif text-2xl tracking-tight">{{ __('storefront.account.settings') }}</h1>
            </div>
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium">{{ __('storefront.account.appearance') }}</p>
                    <p class="mt-1 text-[12px] text-muted-foreground">{{ __('storefront.account.appearance_hint') }}</p>
                </div>
                <x-theme-toggle class="shrink-0 rounded-xl border border-border p-2.5 hover:bg-muted" />
            </div>
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <div class="min-w-0">
                    <p class="text-sm font-medium">{{ __('storefront.account.language') }}</p>
                    <p class="mt-1 text-[12px] text-muted-foreground">{{ __('storefront.header.language') }}</p>
                </div>
                <x-locale-switcher class="shrink-0" />
            </div>
        </x-account.card>
    </x-account.shell>
@endsection
