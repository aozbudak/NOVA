@extends('layouts.admin')

@section('title', __('admin.notifications.title'))

@section('content')
    <x-admin.page-header :title="__('admin.notifications.title')">
        <x-slot:actions>
            @if (collect($notifications)->contains(fn (array $item): bool => $item['unread']))
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <x-admin.button variant="secondary" type="submit">{{ __('admin.notifications.mark_all') }}</x-admin.button>
                </form>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if ($notifications->isEmpty())
        <x-admin.card>
            <x-admin.empty :title="__('admin.empty.notifications.title')">
                {{ __('admin.empty.notifications.body') }}
            </x-admin.empty>
        </x-admin.card>
    @else
        <x-admin.card>
            @foreach ($notifications as $item)
                <div @class([
                    'flex gap-3 border-b border-border px-4 py-3 last:border-b-0',
                    'bg-muted/30' => $item['unread'],
                ])>
                    <span @class([
                        'mt-1.5 size-1.5 shrink-0 rounded-full',
                        'bg-primary' => $item['unread'],
                        'bg-border' => ! $item['unread'],
                    ])></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] text-foreground">{{ $item['title'] }}</p>
                        <p class="text-[12px] text-muted-foreground">{{ $item['body'] }}</p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $item['time'] }}</p>
                    </div>
                    @if ($item['unread'])
                        <form method="POST" action="{{ route('admin.notifications.read', $item['id']) }}">
                            @csrf
                            <button type="submit" class="text-[11px] text-muted-foreground hover:text-foreground">{{ __('admin.notifications.read') }}</button>
                        </form>
                    @else
                        <span class="text-[11px] text-muted-foreground">{{ __('admin.notifications.read_state') }}</span>
                    @endif
                </div>
            @endforeach
        </x-admin.card>
    @endif
@endsection
