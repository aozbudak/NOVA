@extends('layouts.admin')

@section('title', __('admin.users.title'))

@section('content')
    <x-admin.page-header :title="__('admin.users.title')">
        <x-slot:actions>
            <x-admin.button :href="route('admin.users.create')" icon="plus">{{ __('admin.users.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.table :paginator="$users">
        <x-slot:head>
            <x-admin.th sort="name">{{ __('admin.users.user') }}</x-admin.th>
            <x-admin.th sort="username">{{ __('admin.users.username') }}</x-admin.th>
            <x-admin.th>{{ __('admin.users.role') }}</x-admin.th>
            <x-admin.th sort="status">{{ __('admin.users.status') }}</x-admin.th>
            <x-admin.th sort="last_login">{{ __('admin.users.last_login') }}</x-admin.th>
            <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($users as $user)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.users.user')">
                        <x-admin.person :name="$user['name']" :meta="$user['email']" />
                    </x-admin.td>
                    <x-admin.td :label="__('admin.users.username')" tone="muted">{{ $user['username'] ?? '' }}</x-admin.td>
                    <x-admin.td :label="__('admin.users.role')">
                        <span class="rounded-sm bg-muted px-1.5 py-0.5 text-[11px] text-foreground">{{ $user['role_label'] }}</span>
                    </x-admin.td>
                    <x-admin.td :label="__('admin.users.status')"><x-admin.badge group="status" :status="$user['status']" /></x-admin.td>
                    <x-admin.td :label="__('admin.users.last_login')" tone="muted">{{ $user['last_login'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.common.actions')" align="end">
                        <x-admin.row-actions>
                            <x-admin.icon-button icon="edit" :label="__('admin.common.edit')" :href="route('admin.users.edit', $user['id'])" />
                        </x-admin.row-actions>
                    </x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
