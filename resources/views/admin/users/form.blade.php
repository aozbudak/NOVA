@extends('layouts.admin')

@section('title', $user ? __('admin.users.edit') : __('admin.users.add'))

@section('content')
    @php
        $roleKey = old('role', $roleName);
        $selectedAbilities = collect(old('abilities', data_get($user, 'abilities', $roleAbilities[$roleKey] ?? [])))->all();
        $abilitySections = [
            'store' => ['products', 'categories', 'brands', 'variants', 'inventory', 'barcode'],
            'sales_group' => ['pos', 'sales', 'returns', 'exchanges'],
            'people' => ['customers', 'suppliers'],
            'finance' => ['cash', 'income_expense', 'payments'],
            'reporting' => ['reports'],
            'system' => ['roles', 'users', 'audit', 'site', 'settings'],
        ];
    @endphp

    <x-admin.page-header :title="$user ? __('admin.users.edit') : __('admin.users.add')" :description="data_get($user, 'email')">
        <x-slot:actions>
            <x-admin.button variant="ghost" :href="route('admin.roles.index')">{{ __('admin.common.cancel') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ $user ? route('admin.users.update', $user['id']) : route('admin.users.store') }}" class="flex max-w-4xl flex-col gap-6" autocomplete="off">
        @csrf
        @if ($user)
            @method('PUT')
        @endif

        <x-admin.card class="overflow-hidden">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.users.identity') }}</h2>
                <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.users.identity_hint') }}</p>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <x-admin.field :label="__('admin.users.first_name')" name="first_name" required>
                    <x-admin.input name="first_name" value="{{ old('first_name', data_get($user, 'first_name', '')) }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.last_name')" name="last_name" required>
                    <x-admin.input name="last_name" value="{{ old('last_name', data_get($user, 'last_name', '')) }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.email')" name="email" required>
                    <x-admin.input name="email" type="email" value="{{ old('email', data_get($user, 'email', '')) }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.username')" name="username" required>
                    <x-admin.input name="username" value="{{ old('username', data_get($user, 'username', '')) }}" autocomplete="off" required />
                </x-admin.field>
                <x-admin.field class="sm:col-span-2" :label="__('admin.users.phone')" name="phone">
                    <x-admin.input name="phone" value="{{ old('phone', data_get($user, 'phone', '')) }}" />
                </x-admin.field>
            </div>
        </x-admin.card>

        <x-admin.card class="overflow-hidden">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.users.access') }}</h2>
                <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.users.role_hint') }}</p>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <x-admin.field :label="__('admin.users.role')" name="role" required>
                    <x-admin.input name="role" value="{{ $roleKey }}" list="staff-roles" data-user-role data-role-abilities='@json($roleAbilities)' required />
                    <datalist id="staff-roles">
                        @foreach ($roles as $option)
                            <option value="{{ $option['name'] }}"></option>
                        @endforeach
                    </datalist>
                </x-admin.field>
                <x-admin.field :label="__('admin.users.status')" name="status" required>
                    <x-admin.select name="status">
                        <option value="active" @selected(old('status', data_get($user, 'status', 'active')) === 'active')>{{ __('admin.status.active') }}</option>
                        <option value="inactive" @selected(old('status', data_get($user, 'status')) === 'inactive')>{{ __('admin.status.inactive') }}</option>
                    </x-admin.select>
                </x-admin.field>
                <x-admin.field class="sm:col-span-2" :label="__('admin.users.password')" name="password" :required="! $user" :help="$user ? __('admin.users.password_hint') : __('admin.users.password_help')">
                    <x-admin.input name="password" type="password" autocomplete="new-password" value="" @required(! $user) />
                </x-admin.field>
            </div>
        </x-admin.card>

        <x-admin.card class="overflow-hidden">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.users.abilities') }}</h2>
                <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.users.abilities_hint') }}</p>
            </div>
            <div class="flex flex-col gap-5 p-5">
                @foreach ($abilitySections as $section => $keys)
                    @php
                        $items = array_values(array_intersect($keys, $operations));
                    @endphp
                    @if ($items !== [])
                        <div class="flex flex-col gap-2">
                            <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.nav.'.$section) }}</p>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($items as $operation)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-md border border-border px-3 py-2 text-[13px] text-foreground hover:bg-accent has-[:checked]:border-foreground/15 has-[:checked]:bg-accent">
                                        <input type="checkbox" name="abilities[]" value="{{ $operation }}" data-user-ability @checked(in_array($operation, $selectedAbilities, true)) class="size-3.5 rounded-sm border-input">
                                        {{ __('admin.nav.'.$operation) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-admin.card>

        <div class="flex items-center gap-2 border-t border-border pt-4">
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            <x-admin.button variant="ghost" :href="route('admin.roles.index')">{{ __('admin.common.cancel') }}</x-admin.button>
        </div>
    </form>
@endsection
