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

    <section class="mt-6 overflow-hidden rounded-md border border-border bg-card">
        <div class="border-b border-border px-4 py-3">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.roles.users') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                    <tr>
                        <th class="px-3 py-2 font-medium">{{ __('admin.users.user') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('admin.users.email') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('admin.users.status') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('admin.users.last_login') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-border last:border-b-0">
                            <td class="px-3 py-2.5 text-foreground">{{ $user['name'] }}</td>
                            <td class="px-3 py-2.5 text-muted-foreground">{{ $user['email'] }}</td>
                            <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$user['status']" /></td>
                            <td class="px-3 py-2.5 text-muted-foreground">{{ $user['last_login'] }}</td>
                            <td class="px-3 py-2.5">
                                <a href="{{ route('admin.users.edit', $user['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.edit') }}">
                                    <x-icon name="edit" size="size-3.5" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-6 text-center text-muted-foreground">{{ __('admin.roles.empty_users') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
