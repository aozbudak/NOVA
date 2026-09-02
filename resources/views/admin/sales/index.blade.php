@extends('layouts.admin')

@section('title', __('admin.sales.title'))

@section('content')
    <x-admin.page-header :title="__('admin.sales.title')" />

    <form method="GET" action="{{ route('admin.sales.index') }}" class="mb-4 grid gap-2 md:grid-cols-6">
        <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.sales.search') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <input type="date" name="from" value="{{ $filters['from'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" aria-label="{{ __('admin.sales.from') }}">
        <input type="date" name="to" value="{{ $filters['to'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" aria-label="{{ __('admin.sales.to') }}">
        <select name="payment" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.sales.filter_payment') }}</option>
            <option value="cash" @selected($filters['payment'] === 'cash')>{{ __('admin.pos.cash') }}</option>
            <option value="card" @selected($filters['payment'] === 'card')>{{ __('admin.pos.card') }}</option>
            <option value="other" @selected($filters['payment'] === 'other')>{{ __('admin.pos.other') }}</option>
        </select>
        <select name="cashier" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.sales.filter_cashier') }}</option>
            @foreach ($cashiers as $cashier)
                <option value="{{ $cashier }}" @selected($filters['cashier'] === $cashier)>{{ $cashier }}</option>
            @endforeach
        </select>
        <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" onchange="this.form.submit()">
            <option value="">{{ __('admin.sales.filter_status') }}</option>
            <option value="completed" @selected($filters['status'] === 'completed')>{{ __('admin.status.completed') }}</option>
            <option value="cancelled" @selected($filters['status'] === 'cancelled')>{{ __('admin.status.cancelled') }}</option>
            <option value="returned" @selected($filters['status'] === 'returned')>{{ __('admin.status.returned') }}</option>
            <option value="partially_returned" @selected($filters['status'] === 'partially_returned')>{{ __('admin.status.partially_returned') }}</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.number') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.date') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.customer') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.items') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.total') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.payment') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.cashier') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.sales.status') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sales as $sale)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $sale['number'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $sale['date'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $sale['customer'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $sale['items_count'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($sale['total']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ __('admin.pos.'.$sale['payment']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $sale['cashier'] }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$sale['status']" /></td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.sales.show', $sale['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.view') }}">
                                <x-icon name="eye" size="size-3.5" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
