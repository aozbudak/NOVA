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

    <section class="mt-4 rounded-md border border-border bg-card">
        <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.customers.history') }}</h2>
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.reference') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.date') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.items') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customer['sales'] as $sale)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-4 py-2.5 text-foreground">{{ $sale['ref'] }}</td>
                        <td class="px-4 py-2.5 text-muted-foreground">{{ $sale['date'] }}</td>
                        <td class="px-4 py-2.5 text-muted-foreground">{{ $sale['items'] }}</td>
                        <td class="px-4 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($sale['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section class="mt-4 rounded-md border border-border bg-card">
        <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.customers.returns') }}</h2>
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.reference') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.date') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.items') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.customers.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customer['returns'] as $return)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-4 py-2.5 text-foreground">{{ $return['ref'] }}</td>
                        <td class="px-4 py-2.5 text-muted-foreground">{{ $return['date'] }}</td>
                        <td class="px-4 py-2.5 text-muted-foreground">{{ $return['items'] }}</td>
                        <td class="px-4 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($return['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
