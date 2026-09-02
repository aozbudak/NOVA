@extends('layouts.admin')

@section('title', __('admin.roles.title'))

@section('content')
    <x-admin.page-header :title="__('admin.roles.title')">
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                <x-icon name="plus" size="size-3.5" />
                {{ __('admin.users.add') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="flex flex-col gap-6">
        @foreach ($roles as $role)
            <section class="overflow-hidden rounded-md border border-border bg-card">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border px-4 py-3">
                    <h2 class="text-sm font-medium text-foreground">{{ $role['label'] }}</h2>
                    <a href="{{ route('admin.roles.show', $role['key']) }}" class="text-[12px] text-muted-foreground hover:text-foreground">
                        {{ $role['builtin'] ? __('admin.roles.matrix') : __('admin.common.view') }}
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                            <tr>
                                <th class="px-3 py-2 font-medium">{{ __('admin.users.user') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.users.email') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.users.status') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.users.last_login') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.users.created_at') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($role['users'] as $user)
                                <tr class="border-b border-border last:border-b-0">
                                    <td class="px-3 py-2.5 text-foreground">{{ $user['name'] }}</td>
                                    <td class="px-3 py-2.5 text-muted-foreground">{{ $user['email'] }}</td>
                                    <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$user['status']" /></td>
                                    <td class="px-3 py-2.5 text-muted-foreground">{{ $user['last_login'] }}</td>
                                    <td class="px-3 py-2.5 text-muted-foreground">{{ $user['created_at'] }}</td>
                                    <td class="px-3 py-2.5">
                                        <a href="{{ route('admin.users.edit', $user['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.edit') }}">
                                            <x-icon name="edit" size="size-3.5" />
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-muted-foreground">{{ __('admin.roles.empty_users') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
@endsection
