@extends('layouts.admin')

@section('title', __('admin.exchanges.title'))

@section('content')
    <x-admin.page-header :title="__('admin.exchanges.title')" />

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.exchanges.number') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.exchanges.date') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.exchanges.customer') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.exchanges.original') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.exchanges.new') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.exchanges.difference') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($exchanges as $exchange)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $exchange['number'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $exchange['date'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $exchange['customer'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $exchange['original']['product'] }} / {{ $exchange['original']['variant'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $exchange['new']['product'] }} / {{ $exchange['new']['variant'] }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$exchange['difference']" /></td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.exchanges.show', $exchange['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.view') }}">
                                <x-icon name="eye" size="size-3.5" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
