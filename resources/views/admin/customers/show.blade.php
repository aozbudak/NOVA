@extends('layouts.admin')

@section('title', $customer['name'])

@section('content')
    <x-admin.page-header :title="$customer['name']" />

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.customers.profile') }}</h2>
            <dl class="mt-3 flex flex-col gap-2 text-[13px]">
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.customers.name') }}</dt>
                    <dd class="text-foreground">{{ $customer['name'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.customers.city') }}</dt>
                    <dd class="text-foreground">{{ $customer['city'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.customers.orders') }}</dt>
                    <dd class="text-foreground">{{ $customer['orders'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.customers.spent') }}</dt>
                    <dd class="text-foreground">{{ \App\Support\AdminStore::money($customer['spent']) }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-md border border-border bg-card p-4 lg:col-span-2">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.customers.contact') }}</h2>
            <dl class="mt-3 grid gap-2 text-[13px] md:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.customers.phone') }}</dt>
                    <dd class="text-foreground">{{ $customer['phone'] }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.customers.email') }}</dt>
                    <dd class="text-foreground">{{ $customer['email'] }}</dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="mt-4">
        <h2 class="mb-2 text-sm font-medium text-foreground">{{ __('admin.customers.history') }}</h2>
        <x-admin.table>
            <x-slot:head>
                <x-admin.th>{{ __('admin.customers.reference') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.date') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.items') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.total') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($customer['sales'] as $sale)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.customers.reference')">{{ $sale['ref'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.date')" tone="muted">{{ $sale['date'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.items')" tone="muted">{{ $sale['items'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.total')">{{ \App\Support\AdminStore::money($sale['total']) }}</x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    </section>

    <section class="mt-4">
        <h2 class="mb-2 text-sm font-medium text-foreground">{{ __('admin.customers.returns') }}</h2>
        <x-admin.table>
            <x-slot:head>
                <x-admin.th>{{ __('admin.customers.reference') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.date') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.items') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.total') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($customer['returns'] as $return)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.customers.reference')">{{ $return['ref'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.date')" tone="muted">{{ $return['date'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.items')" tone="muted">{{ $return['items'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.total')">{{ \App\Support\AdminStore::money($return['total']) }}</x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    </section>
@endsection
