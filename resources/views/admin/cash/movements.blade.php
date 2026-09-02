@extends('layouts.admin')

@section('title', __('admin.cash.movements'))

@section('content')
    <x-admin.page-header :title="__('admin.cash.movements')" />

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.cash.date') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.cash.type') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.cash.description') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.cash.reference') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.cash.amount') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.cash.balance') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.cash.user') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movements as $row)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['date'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ __('admin.status.'.$row['type']) }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['description'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['reference'] }}</td>
                        <td @class(['px-3 py-2.5', 'text-success' => $row['flow'] === 'in', 'text-destructive' => $row['flow'] === 'out', 'text-muted-foreground' => $row['flow'] === 'neutral'])>
                            {{ $row['amount'] === 0 ? '—' : \App\Support\AdminStore::money($row['amount']) }}
                        </td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($row['balance']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['user'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
