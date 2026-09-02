@extends('layouts.admin')

@section('title', __('admin.cash.movements'))

@section('content')
    <x-admin.page-header :title="__('admin.cash.movements')" />

    <x-admin.table :paginator="$movements">
        <x-slot:head>
            <x-admin.th sort="date">{{ __('admin.cash.date') }}</x-admin.th>
            <x-admin.th>{{ __('admin.cash.type') }}</x-admin.th>
            <x-admin.th>{{ __('admin.cash.description') }}</x-admin.th>
            <x-admin.th>{{ __('admin.cash.reference') }}</x-admin.th>
            <x-admin.th sort="amount" align="end">{{ __('admin.cash.amount') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.cash.balance') }}</x-admin.th>
            <x-admin.th>{{ __('admin.cash.user') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($movements as $row)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.cash.date')" tone="muted">{{ $row['date'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.cash.type')">{{ __('admin.status.'.$row['type']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.cash.description')">{{ $row['description'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.cash.reference')" tone="muted">{{ $row['reference'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.cash.amount')" align="end" :tone="$row['flow'] === 'in' ? 'success' : ($row['flow'] === 'out' ? 'danger' : 'muted')">
                        {{ $row['amount'] === 0 ? '—' : \App\Support\AdminStore::money($row['amount']) }}
                    </x-admin.td>
                    <x-admin.td :label="__('admin.cash.balance')" align="end">{{ \App\Support\AdminStore::money($row['balance']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.cash.user')" tone="muted">{{ $row['user'] }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
