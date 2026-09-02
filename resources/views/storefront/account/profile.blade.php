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
            <div class="flex items-center gap-4 border-b border-border px-5 py-4">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-foreground text-sm font-medium tracking-wide text-background uppercase">
                    {{ $initials !== '' ? $initials : 'N' }}
                </span>
                <div class="min-w-0">
                    <h1 class="truncate font-serif text-2xl tracking-tight">{{ $fullName }}</h1>
                    <p class="truncate text-sm text-muted-foreground">{{ $customer['email'] }}</p>
                </div>
            </div>
            <dl class="grid sm:grid-cols-2">
                <div class="flex flex-col gap-1 px-5 py-4 sm:border-r sm:border-border">
                    <dt class="text-[11px] font-medium tracking-label uppercase text-muted-foreground">{{ __('storefront.account.name') }}</dt>
                    <dd class="text-sm">{{ $fullName }}</dd>
                </div>
                <div class="flex flex-col gap-1 border-t border-border px-5 py-4 sm:border-t-0">
                    <dt class="text-[11px] font-medium tracking-label uppercase text-muted-foreground">{{ __('storefront.account.email') }}</dt>
                    <dd class="truncate text-sm">{{ $customer['email'] }}</dd>
                </div>
            </dl>
        </x-account.card>
    </x-account.shell>
@endsection
