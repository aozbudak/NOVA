@extends('layouts.admin')

@section('title', __('admin.suppliers.title'))

@section('content')
    <x-admin.page-header :title="__('admin.suppliers.title')">
        <x-slot:actions>
            <x-admin.button :href="route('admin.suppliers.create')" icon="plus">{{ __('admin.suppliers.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.suppliers.index')" :chips="$chips" :columns="4">
        <x-admin.input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.suppliers.search') }}" />
        <x-admin.select name="status">
            <option value="">{{ __('admin.suppliers.filter_status') }}</option>
            <option value="active" @selected($filters['status'] === 'active')>{{ __('admin.status.active') }}</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('admin.status.inactive') }}</option>
        </x-admin.select>
        <x-admin.input type="date" name="date" value="{{ $filters['date'] }}" aria-label="{{ __('admin.suppliers.filter_date') }}" />
        <x-admin.select name="sort">
            <option value="">{{ __('admin.suppliers.sort') }}</option>
            <option value="name" @selected($filters['sort'] === 'name')>{{ __('admin.suppliers.sort_name') }}</option>
            <option value="purchases" @selected($filters['sort'] === 'purchases')>{{ __('admin.suppliers.sort_purchases') }}</option>
            <option value="recent" @selected($filters['sort'] === 'recent')>{{ __('admin.suppliers.sort_recent') }}</option>
        </x-admin.select>
    </x-admin.filters>

    @if ($suppliers->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.suppliers.title')">
                {{ __('admin.empty.suppliers.body') }}
                <x-slot:action>
                    <x-admin.button :href="route('admin.suppliers.create')">{{ __('admin.suppliers.add') }}</x-admin.button>
                </x-slot:action>
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$suppliers">
            <x-slot:head>
                <x-admin.th sort="name">{{ __('admin.suppliers.supplier') }}</x-admin.th>
                <x-admin.th>{{ __('admin.suppliers.contact') }}</x-admin.th>
                <x-admin.th>{{ __('admin.suppliers.phone') }}</x-admin.th>
                <x-admin.th>{{ __('admin.suppliers.email') }}</x-admin.th>
                <x-admin.th sort="total" align="end">{{ __('admin.suppliers.purchases') }}</x-admin.th>
                <x-admin.th sort="last_purchase">{{ __('admin.suppliers.last_purchase') }}</x-admin.th>
                <x-admin.th sort="status">{{ __('admin.suppliers.status') }}</x-admin.th>
                <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($suppliers as $supplier)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.suppliers.supplier')">{{ $supplier['name'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.contact')" tone="muted">{{ $supplier['contact'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.phone')" tone="muted">{{ $supplier['phone'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.email')" tone="muted">{{ $supplier['email'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.purchases')" align="end">{{ \App\Support\AdminStore::money($supplier['total']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.last_purchase')" tone="muted">{{ $supplier['last_purchase'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.suppliers.status')"><x-admin.badge group="status" :status="$supplier['status']" /></x-admin.td>
                        <x-admin.td :label="__('admin.common.actions')" align="end">
                            <x-admin.row-actions>
                                <x-admin.icon-button icon="eye" :label="__('admin.common.view')" :href="route('admin.suppliers.show', $supplier['id'])" />
                            </x-admin.row-actions>
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    @endif
@endsection
