@extends('layouts.admin')

@section('title', __('admin.sales.title'))

@section('content')
    <x-admin.page-header :title="__('admin.sales.title')" />

    <x-admin.filters :action="route('admin.sales.index')" :chips="$chips" :columns="6">
        <x-admin.input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.sales.search') }}" />
        <x-admin.input type="date" name="from" value="{{ $filters['from'] }}" aria-label="{{ __('admin.sales.from') }}" />
        <x-admin.input type="date" name="to" value="{{ $filters['to'] }}" aria-label="{{ __('admin.sales.to') }}" />
        <x-admin.select name="payment">
            <option value="">{{ __('admin.sales.filter_payment') }}</option>
            <option value="cash" @selected($filters['payment'] === 'cash')>{{ __('admin.pos.cash') }}</option>
            <option value="card" @selected($filters['payment'] === 'card')>{{ __('admin.pos.card') }}</option>
            <option value="other" @selected($filters['payment'] === 'other')>{{ __('admin.pos.other') }}</option>
        </x-admin.select>
        <x-admin.select name="cashier">
            <option value="">{{ __('admin.sales.filter_cashier') }}</option>
            @foreach ($cashiers as $cashier)
                <option value="{{ $cashier }}" @selected($filters['cashier'] === $cashier)>{{ $cashier }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.select name="status">
            <option value="">{{ __('admin.sales.filter_status') }}</option>
            <option value="completed" @selected($filters['status'] === 'completed')>{{ __('admin.status.completed') }}</option>
            <option value="cancelled" @selected($filters['status'] === 'cancelled')>{{ __('admin.status.cancelled') }}</option>
            <option value="returned" @selected($filters['status'] === 'returned')>{{ __('admin.status.returned') }}</option>
            <option value="partially_returned" @selected($filters['status'] === 'partially_returned')>{{ __('admin.status.partially_returned') }}</option>
        </x-admin.select>
    </x-admin.filters>

    @if ($sales->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.sales.title')">
                {{ __('admin.empty.sales.body') }}
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$sales">
            <x-slot:head>
                <x-admin.th sort="number">{{ __('admin.sales.number') }}</x-admin.th>
                <x-admin.th sort="date">{{ __('admin.sales.date') }}</x-admin.th>
                <x-admin.th sort="customer">{{ __('admin.sales.customer') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.items') }}</x-admin.th>
                <x-admin.th sort="total" align="end">{{ __('admin.sales.total') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.payment') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.cashier') }}</x-admin.th>
                <x-admin.th sort="status">{{ __('admin.sales.status') }}</x-admin.th>
                <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($sales as $sale)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.sales.number')">{{ $sale['number'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.date')" tone="muted">{{ $sale['date'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.customer')">{{ $sale['customer'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.items')" tone="muted">{{ $sale['items_count'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.total')" align="end">{{ \App\Support\AdminStore::money($sale['total']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.payment')" tone="muted">{{ __('admin.pos.'.$sale['payment']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.cashier')" tone="muted">{{ $sale['cashier'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.status')"><x-admin.badge group="status" :status="$sale['status']" /></x-admin.td>
                        <x-admin.td :label="__('admin.common.actions')" align="end">
                            <x-admin.row-actions>
                                <x-admin.icon-button icon="eye" :label="__('admin.common.view')" :href="route('admin.sales.show', $sale['id'])" />
                            </x-admin.row-actions>
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    @endif
@endsection
