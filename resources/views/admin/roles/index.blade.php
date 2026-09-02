@extends('layouts.admin')

@section('title', __('admin.roles.title'))

@section('content')
    <x-admin.page-header :title="__('admin.roles.title')">
        <x-slot:actions>
            <x-admin.button :href="route('admin.users.create')" icon="plus">{{ __('admin.users.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="flex flex-col gap-6">
        @foreach ($roles as $role)
            <section>
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-sm font-medium text-foreground">{{ $role['label'] }}</h2>
                    <a href="{{ route('admin.roles.show', $role['key']) }}" class="text-[12px] text-muted-foreground hover:text-foreground">
                        {{ $role['builtin'] ? __('admin.roles.matrix') : __('admin.common.view') }}
                    </a>
                </div>

                @if ($role['users']->isEmpty())
                    <x-admin.table empty>
                        <x-admin.empty :title="__('admin.roles.empty_users')" />
                    </x-admin.table>
                @else
                    <x-admin.table>
                        <x-slot:head>
                            <x-admin.th>{{ __('admin.users.user') }}</x-admin.th>
                            <x-admin.th>{{ __('admin.users.username') }}</x-admin.th>
                            <x-admin.th>{{ __('admin.users.email') }}</x-admin.th>
                            <x-admin.th>{{ __('admin.users.status') }}</x-admin.th>
                            <x-admin.th>{{ __('admin.users.last_login') }}</x-admin.th>
                            <x-admin.th>{{ __('admin.users.created_at') }}</x-admin.th>
                            <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
                        </x-slot:head>
                        <x-slot:body>
                            @foreach ($role['users'] as $user)
                                <tr class="border-b border-border last:border-b-0">
                                    <x-admin.td :label="__('admin.users.user')">{{ $user['name'] }}</x-admin.td>
                                    <x-admin.td :label="__('admin.users.username')" tone="muted">{{ $user['username'] ?? '' }}</x-admin.td>
                                    <x-admin.td :label="__('admin.users.email')" tone="muted">{{ $user['email'] }}</x-admin.td>
                                    <x-admin.td :label="__('admin.users.status')"><x-admin.badge group="status" :status="$user['status']" /></x-admin.td>
                                    <x-admin.td :label="__('admin.users.last_login')" tone="muted">{{ $user['last_login'] }}</x-admin.td>
                                    <x-admin.td :label="__('admin.users.created_at')" tone="muted">{{ $user['created_at'] }}</x-admin.td>
                                    <x-admin.td :label="__('admin.common.actions')" align="end">
                                        <x-admin.row-actions>
                                            <x-admin.icon-button icon="edit" :label="__('admin.common.edit')" :href="route('admin.users.edit', $user['id'])" />
                                        </x-admin.row-actions>
                                    </x-admin.td>
                                </tr>
                            @endforeach
                        </x-slot:body>
                    </x-admin.table>
                @endif
            </section>
        @endforeach
    </div>
@endsection
