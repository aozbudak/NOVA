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

    <section class="mt-4 rounded-md border border-border bg-card">
        <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.suppliers.history') }}</h2>
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-4 py-2 font-medium">{{ __('admin.suppliers.number') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.suppliers.date') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.suppliers.products') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.suppliers.total') }}</th>
                    <th class="px-4 py-2 font-medium">{{ __('admin.suppliers.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supplier['history'] as $purchase)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-4 py-2.5 text-foreground">{{ $purchase['number'] }}</td>
                        <td class="px-4 py-2.5 text-muted-foreground">{{ $purchase['date'] }}</td>
                        <td class="px-4 py-2.5 text-muted-foreground">{{ $purchase['products'] }}</td>
                        <td class="px-4 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($purchase['total']) }}</td>
                        <td class="px-4 py-2.5"><x-admin.badge group="status" :status="$purchase['status']" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    @if ($supplier['movements'] !== [])
        <section class="mt-4 rounded-md border border-border bg-card">
            <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.suppliers.movements') }}</h2>
            <table class="w-full text-left text-[13px]">
                <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                    <tr>
                        <th class="px-4 py-2 font-medium">{{ __('admin.inventory.date') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('admin.inventory.product') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('admin.inventory.quantity') }}</th>
                        <th class="px-4 py-2 font-medium">{{ __('admin.inventory.reference') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($supplier['movements'] as $row)
                        <tr class="border-b border-border last:border-b-0">
                            <td class="px-4 py-2.5 text-muted-foreground">{{ $row['date'] }}</td>
                            <td class="px-4 py-2.5 text-foreground">{{ $row['product'] }}</td>
                            <td class="px-4 py-2.5 text-success">+{{ $row['qty'] }}</td>
                            <td class="px-4 py-2.5 text-muted-foreground">{{ $row['reference'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif
@endsection
