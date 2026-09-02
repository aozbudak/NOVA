@extends('layouts.admin')

@section('title', __('admin.inventory.movements'))

@section('content')
    <x-admin.page-header :title="__('admin.inventory.movements')" />

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.date') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.product') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.type') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.quantity') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.before') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.after') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.user') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.reference') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($movements as $row)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['date'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['product'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ __('admin.inventory.types.'.$row['type']) }}</td>
                        <td @class(['px-3 py-2.5', 'text-destructive' => $row['qty'] < 0, 'text-success' => $row['qty'] > 0])>{{ $row['qty'] > 0 ? '+'.$row['qty'] : $row['qty'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['before'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['after'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['user'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['reference'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
