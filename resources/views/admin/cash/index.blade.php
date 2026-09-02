@extends('layouts.admin')

@section('title', __('admin.cash.title'))

@section('content')
    <x-admin.page-header :title="__('admin.cash.title')">
        <x-slot:actions>
            <a href="{{ route('admin.cash.movements') }}" class="inline-flex h-8 items-center rounded-md border border-border bg-card px-3 text-[12px] text-foreground hover:bg-accent">
                {{ __('admin.cash.movements') }}
            </a>
            <a href="{{ route('admin.cash.open') }}" class="inline-flex h-8 items-center rounded-md border border-border bg-card px-3 text-[12px] text-foreground hover:bg-accent">
                {{ __('admin.cash.open') }}
            </a>
            <a href="{{ route('admin.cash.close') }}" class="inline-flex h-8 items-center rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                {{ __('admin.cash.close') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => __('admin.cash.opening'), 'value' => \App\Support\AdminStore::money($register['opening'])],
            ['label' => __('admin.cash.current'), 'value' => \App\Support\AdminStore::money($register['current'])],
            ['label' => __('admin.cash.today_sales'), 'value' => \App\Support\AdminStore::money($register['today_sales'])],
            ['label' => __('admin.cash.today_expenses'), 'value' => \App\Support\AdminStore::money($register['today_expenses'])],
            ['label' => __('admin.cash.today_refunds'), 'value' => \App\Support\AdminStore::money($register['today_refunds'])],
        ] as $kpi)
            <section class="rounded-md border border-border bg-card p-4">
                <p class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ $kpi['label'] }}</p>
                <p class="mt-2 font-serif text-2xl tracking-tight text-foreground">{{ $kpi['value'] }}</p>
            </section>
        @endforeach
    </div>
@endsection
