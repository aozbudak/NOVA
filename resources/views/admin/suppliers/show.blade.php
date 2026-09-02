@extends('layouts.admin')

@section('title', $supplier['name'])

@section('content')
    <x-admin.page-header :title="$supplier['name']" />

    <div class="grid gap-4 lg:grid-cols-3">
        <section class="rounded-md border border-border bg-card p-4 lg:col-span-2">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.suppliers.general') }}</h2>
            <dl class="mt-3 grid gap-3 text-[13px] md:grid-cols-2">
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.company') }}</dt>
                    <dd class="text-foreground">{{ $supplier['name'] }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.contact') }}</dt>
                    <dd class="text-foreground">{{ $supplier['contact'] }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.phone') }}</dt>
                    <dd class="text-foreground">{{ $supplier['phone'] }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.email') }}</dt>
                    <dd class="text-foreground">{{ $supplier['email'] }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.address') }}</dt>
                    <dd class="text-foreground">{{ $supplier['address'] }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.tax') }}</dt>
                    <dd class="text-foreground">{{ $supplier['tax'] }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.suppliers.summary') }}</h2>
            <dl class="mt-3 flex flex-col gap-2 text-[13px]">
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.purchases') }}</dt>
                    <dd class="text-foreground">{{ $supplier['purchases'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.total_amount') }}</dt>
                    <dd class="text-foreground">{{ \App\Support\AdminStore::money($supplier['total']) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.suppliers.last_purchase') }}</dt>
                    <dd class="text-foreground">{{ $supplier['last_purchase'] }}</dd>
                </div>
                @if ($supplier['balance'] > 0)
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('admin.suppliers.balance') }}</dt>
                        <dd class="text-foreground">{{ \App\Support\AdminStore::money($supplier['balance']) }}</dd>
                    </div>
                @endif
            </dl>
        </section>
    </div>

    <section class="mt-4">
        <h2 class="mb-2 text-sm font-medium text-foreground">{{ __('admin.suppliers.history') }}</h2>
        <x-admin.table>
            <x-slot:head>
                <x-admin.th>{{ __('admin.suppliers.number') }}</x-admin.th>
                <x-admin.th>{{ __('admin.suppliers.date') }}</x-admin.th>
                <x-admin.th>{{ __('admin.suppliers.products') }}</x-admin.th>
                <x-admin.th>{{ __('admin.suppliers.total') }}</x-admin.th>
                <x-admin.th>{{ __('admin.suppliers.status') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($supplier['history'] as $purchase)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.suppliers.number')">{{ $purchase['number'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.date')" tone="muted">{{ $purchase['date'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.products')" tone="muted">{{ $purchase['products'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.total')">{{ \App\Support\AdminStore::money($purchase['total']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.status')"><x-admin.badge group="status" :status="$purchase['status']" /></x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    </section>

    @if ($supplier['movements'] !== [])
        <section class="mt-4">
            <h2 class="mb-2 text-sm font-medium text-foreground">{{ __('admin.suppliers.movements') }}</h2>
            <x-admin.table>
                <x-slot:head>
                    <x-admin.th>{{ __('admin.inventory.date') }}</x-admin.th>
                    <x-admin.th>{{ __('admin.inventory.product') }}</x-admin.th>
                    <x-admin.th>{{ __('admin.inventory.quantity') }}</x-admin.th>
                    <x-admin.th>{{ __('admin.inventory.reference') }}</x-admin.th>
                </x-slot:head>
                <x-slot:body>
                    @foreach ($supplier['movements'] as $row)
                        <tr class="border-b border-border last:border-b-0">
                            <x-admin.td :label="__('admin.inventory.date')" tone="muted">{{ $row['date'] }}</x-admin.td>
                            <x-admin.td :label="__('admin.inventory.product')">{{ $row['product'] }}</x-admin.td>
                            <x-admin.td :label="__('admin.inventory.quantity')" tone="success">+{{ $row['qty'] }}</x-admin.td>
                            <x-admin.td :label="__('admin.inventory.reference')" tone="muted">{{ $row['reference'] }}</x-admin.td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-admin.table>
        </section>
    @endif
@endsection
