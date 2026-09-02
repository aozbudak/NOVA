@extends('layouts.admin')

@section('title', 'SALE #'.$sale['number'])

@section('content')
    <x-admin.page-header :title="'SALE #'.$sale['number']" />

    <section class="admin-card rounded-2xl border p-4">
        <dl class="grid gap-3 text-[13px] md:grid-cols-5">
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.sale_number') }}</dt>
                <dd class="text-foreground">{{ $sale['number'] }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.date') }}</dt>
                <dd class="text-foreground">{{ $sale['date'] }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.cashier') }}</dt>
                <dd class="text-foreground">{{ $sale['cashier'] }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.customer') }}</dt>
                <dd class="text-foreground">{{ $sale['customer'] }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.payment') }}</dt>
                <dd class="text-foreground">{{ __('admin.pos.'.$sale['payment']) }}</dd>
            </div>
        </dl>
    </section>

    <section class="mt-4">
        <x-admin.table>
            <x-slot:head>
                <x-admin.th>{{ __('admin.sales.product') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.variant') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.sku') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.quantity') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.unit') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.discount') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.total') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($sale['items'] as $item)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.sales.product')">{{ $item['product'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.variant')" tone="muted">{{ $item['variant'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.sku')" tone="muted">{{ $item['sku'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.quantity')">{{ $item['qty'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.unit')">{{ \App\Support\AdminStore::money($item['unit']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.discount')" tone="muted">{{ \App\Support\AdminStore::money($item['discount']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.total')">{{ \App\Support\AdminStore::money($item['total']) }}</x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
        <dl class="ml-auto flex max-w-xs flex-col gap-1.5 px-4 py-3 text-[13px]">
            <div class="flex justify-between">
                <dt class="text-muted-foreground">{{ __('admin.sales.subtotal') }}</dt>
                <dd class="text-foreground">{{ \App\Support\AdminStore::money($sale['subtotal']) }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-muted-foreground">{{ __('admin.sales.discount') }}</dt>
                <dd class="text-foreground">{{ \App\Support\AdminStore::money($sale['discount']) }}</dd>
            </div>
            <div class="flex justify-between font-medium">
                <dt class="text-foreground">{{ __('admin.sales.total') }}</dt>
                <dd class="text-foreground">{{ \App\Support\AdminStore::money($sale['total']) }}</dd>
            </div>
        </dl>
    </section>

    <section class="mt-4 admin-card rounded-2xl border p-4">
        <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.sales.payment_info') }}</h2>
        <dl class="mt-3 grid gap-3 text-[13px] md:grid-cols-3">
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.payment') }}</dt>
                <dd class="text-foreground">{{ __('admin.pos.'.$sale['payment']) }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.total') }}</dt>
                <dd class="text-foreground">{{ \App\Support\AdminStore::money($sale['total']) }}</dd>
            </div>
            <div>
                <dt class="text-muted-foreground">{{ __('admin.sales.status') }}</dt>
                <dd><x-admin.badge group="status" :status="$sale['status']" /></dd>
            </div>
        </dl>
    </section>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="admin-card rounded-2xl border">
            <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.sales.stock_effects') }}</h2>
            <ul class="flex flex-col gap-2 px-4 py-3 text-[13px]">
                @forelse ($sale['stock_effects'] as $row)
                    <li class="flex justify-between gap-3">
                        <span class="text-foreground">{{ $row['product'] }}</span>
                        <span class="text-destructive">{{ $row['qty'] }} · {{ $row['before'] }} → {{ $row['after'] }}</span>
                    </li>
                @empty
                    <li class="text-muted-foreground">—</li>
                @endforelse
            </ul>
        </section>
        <section class="admin-card rounded-2xl border">
            <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.sales.cash_effects') }}</h2>
            <ul class="flex flex-col gap-2 px-4 py-3 text-[13px]">
                @forelse ($sale['cash_effects'] as $row)
                    <li class="flex justify-between gap-3">
                        <span class="text-foreground">{{ $row['description'] }}</span>
                        <span class="text-success">{{ \App\Support\AdminStore::money($row['amount']) }}</span>
                    </li>
                @empty
                    <li class="text-muted-foreground">—</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
