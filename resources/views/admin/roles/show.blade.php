@extends('layouts.admin')

@section('title', $role['name'])

@section('content')
    <x-admin.page-header :title="$role['name']" :description="$role['builtin'] ? __('admin.roles.matrix') : __('admin.roles.custom_hint')" />

    @if ($matrix !== null)
        <div class="overflow-x-auto rounded-md border border-border bg-card">
            <table class="w-full text-left text-[13px]">
                <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                    <tr>
                        <th class="px-3 py-2 font-medium"></th>
                        @foreach ($actions as $action)
                            <th class="px-3 py-2 text-center font-medium">{{ __('admin.roles.actions.'.$action) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groups as $group)
                        <tr class="border-b border-border last:border-b-0">
                            <td class="px-3 py-2.5 text-foreground">{{ __('admin.roles.groups.'.$group) }}</td>
                            @foreach ($actions as $action)
                                <td class="px-3 py-2.5 text-center">
                                    @if ($matrix[$group][$action] ?? false)
                                        <span class="text-success" aria-label="{{ __('admin.roles.granted') }}">✓</span>
                                    @else
                                        <span class="text-muted-foreground" aria-label="{{ __('admin.roles.denied') }}">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <section class="overflow-hidden rounded-md border border-border bg-card">
            <div class="border-b border-border px-4 py-3">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.users.abilities') }}</h2>
            </div>
            <ul class="grid gap-2 p-4 sm:grid-cols-2 md:grid-cols-3">
                @foreach ($operations as $operation)
                    <li class="flex items-center gap-2 text-[13px] text-foreground">
                        @if (in_array($operation, $role['abilities'], true))
                            <span class="text-success" aria-label="{{ __('admin.roles.granted') }}">✓</span>
                        @else
                            <span class="text-muted-foreground" aria-label="{{ __('admin.roles.denied') }}">-</span>
                        @endif
                        {{ __('admin.nav.'.$operation) }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="mt-6">
        <h2 class="mb-2 text-sm font-medium text-foreground">{{ __('admin.roles.users') }}</h2>
        @if ($users->isEmpty())
            <x-admin.table empty>
                <x-admin.empty :title="__('admin.roles.empty_users')" />
            </x-admin.table>
        @else
            <x-admin.table>
                <x-slot:head>
                    <x-admin.th>{{ __('admin.users.user') }}</x-admin.th>
                    <x-admin.th>{{ __('admin.users.email') }}</x-admin.th>
                    <x-admin.th>{{ __('admin.users.status') }}</x-admin.th>
                    <x-admin.th>{{ __('admin.users.last_login') }}</x-admin.th>
                    <x-admin.th align="end">{{ __('admin.common.actions') }}</x-admin.th>
                </x-slot:head>
                <x-slot:body>
                    @foreach ($users as $user)
                        <tr class="border-b border-border last:border-b-0">
                            <x-admin.td :label="__('admin.users.user')">
                                <x-admin.person :name="$user['name']" :meta="$user['email']" />
                            </x-admin.td>
                            <x-admin.td :label="__('admin.users.email')" tone="muted">{{ $user['email'] }}</x-admin.td>
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
        @endif
    </section>
@endsection
