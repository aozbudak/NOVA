@extends('layouts.admin')

@section('title', __('admin.inventory.movements'))

@section('content')
    <x-admin.page-header :title="__('admin.inventory.movements')" />

    <x-admin.table :paginator="$movements">
        <x-slot:head>
            <x-admin.th sort="date">{{ __('admin.inventory.date') }}</x-admin.th>
            <x-admin.th sort="product">{{ __('admin.inventory.product') }}</x-admin.th>
            <x-admin.th sort="type">{{ __('admin.inventory.type') }}</x-admin.th>
            <x-admin.th sort="qty" align="end">{{ __('admin.inventory.quantity') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.inventory.before') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.inventory.after') }}</x-admin.th>
            <x-admin.th>{{ __('admin.inventory.user') }}</x-admin.th>
            <x-admin.th>{{ __('admin.inventory.reference') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($movements as $row)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.inventory.date')" tone="muted">{{ $row['date'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.product')">{{ $row['product'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.type')">{{ __('admin.inventory.types.'.$row['type']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.quantity')" align="end" :tone="$row['qty'] < 0 ? 'danger' : 'success'">{{ $row['qty'] > 0 ? '+'.$row['qty'] : $row['qty'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.before')" align="end" tone="muted">{{ $row['before'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.after')" align="end">{{ $row['after'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.user')" tone="muted">{{ $row['user'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.inventory.reference')" tone="muted">{{ $row['reference'] }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
