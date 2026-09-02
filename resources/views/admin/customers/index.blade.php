@extends('layouts.admin')

@section('title', __('admin.customers.title'))

@section('content')
    <x-admin.page-header :title="__('admin.customers.title')" />

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.name') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.phone') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.email') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.orders') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.spent') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.last_purchase') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $customer['name'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $customer['phone'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $customer['email'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $customer['orders'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($customer['spent']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $customer['last_purchase'] }}</td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.customers.show', $customer['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.view') }}">
                                <x-icon name="eye" size="size-3.5" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
