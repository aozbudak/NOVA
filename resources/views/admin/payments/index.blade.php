@extends('layouts.admin')

@section('title', __('admin.payments.title'))

@section('content')
    <x-admin.page-header :title="__('admin.payments.title')" />

    <x-admin.table :paginator="$payments">
        <x-slot:head>
            <x-admin.th sort="date">{{ __('admin.payments.date') }}</x-admin.th>
            <x-admin.th>{{ __('admin.payments.sale') }}</x-admin.th>
            <x-admin.th>{{ __('admin.payments.customer') }}</x-admin.th>
            <x-admin.th>{{ __('admin.payments.method') }}</x-admin.th>
            <x-admin.th sort="amount" align="end">{{ __('admin.payments.amount') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.payments.status') }}</x-admin.th>
            <x-admin.th>{{ __('admin.payments.reference') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($payments as $payment)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.payments.date')" tone="muted">{{ $payment['date'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.payments.sale')">{{ $payment['sale'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.payments.customer')">{{ $payment['customer'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.payments.method')" tone="muted">{{ __('admin.pos.'.$payment['method']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.payments.amount')" align="end">{{ \App\Support\AdminStore::money($payment['amount']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.payments.status')"><x-admin.badge group="status" :status="$payment['status']" /></x-admin.td>
                    <x-admin.td :label="__('admin.payments.reference')" tone="muted">{{ $payment['reference'] }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
