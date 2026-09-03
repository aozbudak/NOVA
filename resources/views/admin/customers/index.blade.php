@extends('layouts.admin')

@section('title', __('admin.customers.title'))

@section('content')
    <x-admin.page-header :title="__('admin.customers.title')">
        <x-slot:actions>
            <x-admin.button type="button" icon="plus" data-open-layer="add-customer">{{ __('admin.customers.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.customers.index')" :chips="$chips" :columns="1">
        <x-admin.input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.customers.search') }}" />
    </x-admin.filters>

    @if ($customers->isEmpty())
        <x-admin.table empty>
            <x-admin.empty :title="__('admin.empty.customers.title')">
                {{ __('admin.empty.customers.body') }}
                <x-slot:action>
                    <x-admin.button type="button" data-open-layer="add-customer">{{ __('admin.customers.add') }}</x-admin.button>
                </x-slot:action>
            </x-admin.empty>
        </x-admin.table>
    @else
        <x-admin.table :paginator="$customers">
            <x-slot:head>
                <x-admin.th sort="name">{{ __('admin.customers.name') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.phone') }}</x-admin.th>
                <x-admin.th>{{ __('admin.customers.email') }}</x-admin.th>
                <x-admin.th sort="orders" align="end">{{ __('admin.customers.orders') }}</x-admin.th>
                <x-admin.th sort="spent" align="end">{{ __('admin.customers.spent') }}</x-admin.th>
                <x-admin.th sort="last_purchase">{{ __('admin.customers.last_purchase') }}</x-admin.th>
                <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($customers as $customer)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.customers.name')">{{ $customer['name'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.phone')" tone="muted">{{ $customer['phone'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.email')" tone="muted">{{ $customer['email'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.orders')" align="end">{{ $customer['orders'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.spent')" align="end">{{ \App\Support\AdminStore::money($customer['spent']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.customers.last_purchase')" tone="muted">{{ $customer['last_purchase'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.common.actions')" align="end">
                            <x-admin.row-actions>
                                <x-admin.icon-button icon="eye" :label="__('admin.common.view')" :href="route('admin.customers.show', $customer['id'])" />
                            </x-admin.row-actions>
                        </x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>
    @endif

    <x-admin.drawer name="add-customer" :title="__('admin.customers.add')">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="flex flex-col gap-4">
            @csrf
            <x-admin.field :label="__('admin.customers.name')" name="name" required>
                <x-admin.input name="name" required />
            </x-admin.field>
            <x-admin.field :label="__('admin.customers.email')" name="email" required>
                <x-admin.input name="email" type="email" required />
            </x-admin.field>
            <x-admin.field :label="__('admin.customers.phone')" name="phone">
                <x-admin.input name="phone" />
            </x-admin.field>
            <x-admin.field :label="__('admin.customers.password')" name="password" required>
                <x-admin.input name="password" type="password" required minlength="8" />
            </x-admin.field>
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
        </form>
    </x-admin.drawer>
@endsection
