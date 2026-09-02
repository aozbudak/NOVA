@extends('layouts.admin')

@section('title', __('admin.exchanges.title'))

@section('content')
    <x-admin.page-header :title="__('admin.exchanges.title')" />

    <x-admin.table :paginator="$exchanges">
        <x-slot:head>
            <x-admin.th sort="number">{{ __('admin.exchanges.number') }}</x-admin.th>
            <x-admin.th sort="date">{{ __('admin.exchanges.date') }}</x-admin.th>
            <x-admin.th>{{ __('admin.exchanges.customer') }}</x-admin.th>
            <x-admin.th>{{ __('admin.exchanges.original') }}</x-admin.th>
            <x-admin.th>{{ __('admin.exchanges.new') }}</x-admin.th>
            <x-admin.th>{{ __('admin.exchanges.difference') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($exchanges as $exchange)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.exchanges.number')">{{ $exchange['number'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.exchanges.date')" tone="muted">{{ $exchange['date'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.exchanges.customer')">{{ $exchange['customer'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.exchanges.original')" tone="muted">{{ $exchange['original']['product'] }} / {{ $exchange['original']['variant'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.exchanges.new')" tone="muted">{{ $exchange['new']['product'] }} / {{ $exchange['new']['variant'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.exchanges.difference')"><x-admin.badge group="status" :status="$exchange['difference']" /></x-admin.td>
                    <x-admin.td :label="__('admin.common.actions')" align="end">
                        <x-admin.row-actions>
                            <x-admin.icon-button icon="eye" :label="__('admin.common.view')" :href="route('admin.exchanges.show', $exchange['id'])" />
                        </x-admin.row-actions>
                    </x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
