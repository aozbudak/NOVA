@extends('layouts.admin')

@section('title', 'SALE #'.$sale['number'])

@section('content')
    <x-admin.page-header :title="'SALE #'.$sale['number']" />

    <section class="rounded-md border border-border bg-card p-4">
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

    <section class="mt-4 overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.product') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.variant') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.sku') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.quantity') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.unit') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.discount') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale['items'] as $item)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $item['product'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $item['variant'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $item['sku'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $item['qty'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($item['unit']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ \App\Support\AdminStore::money($item['discount']) }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($item['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <dl class="ml-auto flex max-w-xs flex-col gap-1.5 border-t border-border px-4 py-3 text-[13px]">
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

    <section class="mt-4 rounded-md border border-border bg-card p-4">
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
        <section class="rounded-md border border-border bg-card">
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
        <section class="rounded-md border border-border bg-card">
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
