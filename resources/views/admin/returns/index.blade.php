@extends('layouts.admin')

@section('title', __('admin.returns.title'))

@section('content')
    <x-admin.page-header :title="__('admin.returns.title')">
        <x-slot:actions>
            <x-admin.button :href="route('admin.returns.create')" icon="plus">{{ __('admin.returns.create') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.returns.index')" :chips="$chips" :columns="6">
        <x-admin.input type="search" name="return" value="{{ $filters['return'] }}" placeholder="{{ __('admin.returns.filter_return') }}" />
        <x-admin.input type="search" name="sale" value="{{ $filters['sale'] }}" placeholder="{{ __('admin.returns.filter_sale') }}" />
        <x-admin.input type="search" name="customer" value="{{ $filters['customer'] }}" placeholder="{{ __('admin.returns.filter_customer') }}" />
        <x-admin.input type="date" name="date" value="{{ $filters['date'] }}" aria-label="{{ __('admin.returns.date') }}" />
        <x-admin.select name="reason">
            <option value="">{{ __('admin.returns.reason') }}</option>
            <option value="customer_changed_mind" @selected($filters['reason'] === 'customer_changed_mind')>{{ __('admin.status.customer_changed_mind') }}</option>
            <option value="wrong_size" @selected($filters['reason'] === 'wrong_size')>{{ __('admin.status.wrong_size') }}</option>
            <option value="defective" @selected($filters['reason'] === 'defective')>{{ __('admin.status.defective') }}</option>
            <option value="wrong_product" @selected($filters['reason'] === 'wrong_product')>{{ __('admin.status.wrong_product') }}</option>
            <option value="exchange" @selected($filters['reason'] === 'exchange')>{{ __('admin.status.exchange') }}</option>
            <option value="other" @selected($filters['reason'] === 'other')>{{ __('admin.status.other') }}</option>
        </x-admin.select>
        <x-admin.select name="status">
            <option value="">{{ __('admin.returns.status') }}</option>
            <option value="completed" @selected($filters['status'] === 'completed')>{{ __('admin.status.completed') }}</option>
            <option value="open" @selected($filters['status'] === 'open')>{{ __('admin.status.open') }}</option>
        </x-admin.select>
    </x-admin.filters>

    @if ($returns->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.returns.title')">
                {{ __('admin.empty.returns.body') }}
                <x-slot:action>
                    <x-admin.button :href="route('admin.returns.create')">{{ __('admin.returns.create') }}</x-admin.button>
                </x-slot:action>
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$returns">
            <x-slot:head>
                <x-admin.th sort="number">{{ __('admin.returns.number') }}</x-admin.th>
                <x-admin.th>{{ __('admin.returns.sale') }}</x-admin.th>
                <x-admin.th>{{ __('admin.returns.customer') }}</x-admin.th>
                <x-admin.th>{{ __('admin.returns.products') }}</x-admin.th>
                <x-admin.th sort="amount" align="end">{{ __('admin.returns.amount') }}</x-admin.th>
                <x-admin.th>{{ __('admin.returns.reason') }}</x-admin.th>
                <x-admin.th>{{ __('admin.returns.user') }}</x-admin.th>
                <x-admin.th sort="date">{{ __('admin.returns.date') }}</x-admin.th>
                <x-admin.th sort="status">{{ __('admin.returns.status') }}</x-admin.th>
                <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($returns as $row)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.returns.number')">{{ $row['number'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.sale')" tone="muted">{{ $row['sale'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.customer')">{{ $row['customer'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.products')" tone="muted">{{ $row['products'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.amount')" align="end">{{ \App\Support\AdminStore::money($row['amount']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.reason')" tone="muted">{{ __('admin.status.'.$row['reason']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.user')" tone="muted">{{ $row['user'] ?? '—' }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.date')" tone="muted">{{ $row['date'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.returns.status')"><x-admin.badge group="status" :status="$row['status']" /></x-admin.td>
                        <x-admin.td :label="__('admin.common.actions')" align="end">
                            <x-admin.row-actions>
                                <x-admin.icon-button icon="eye" :label="__('admin.common.view')" :href="route('admin.returns.show', $row['id'])" />
                            </x-admin.row-actions>
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    @endif
@endsection
