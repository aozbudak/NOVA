@extends('layouts.admin')

@section('title', __('admin.payments.title'))

@section('content')
    <x-admin.page-header :title="__('admin.payments.title')" />

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.payments.date') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.payments.sale') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.payments.customer') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.payments.method') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.payments.amount') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.payments.status') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.payments.reference') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $payment['date'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $payment['sale'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $payment['customer'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ __('admin.pos.'.$payment['method']) }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($payment['amount']) }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$payment['status']" /></td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $payment['reference'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
