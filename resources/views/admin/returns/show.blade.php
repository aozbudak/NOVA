@extends('layouts.admin')

@section('title', $return['number'])

@section('content')
    <x-admin.page-header :title="$return['number']" />

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="admin-card rounded-2xl border p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.returns.record') }}</h2>
            <dl class="mt-3 flex flex-col gap-2 text-[13px]">
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.number') }}</dt>
                    <dd class="text-foreground">{{ $return['number'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.sale') }}</dt>
                    <dd class="text-foreground">{{ $return['sale'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.customer') }}</dt>
                    <dd class="text-foreground">{{ $return['customer'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.products') }}</dt>
                    <dd class="text-right text-foreground">{{ $return['products'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.type') }}</dt>
                    <dd class="text-foreground">{{ __('admin.status.'.$return['type']) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.reason') }}</dt>
                    <dd class="text-foreground">{{ __('admin.status.'.$return['reason']) }}</dd>
                </div>
                @if (($return['notes'] ?? '') !== '')
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('admin.returns.notes') }}</dt>
                        <dd class="text-right text-foreground">{{ $return['notes'] }}</dd>
                    </div>
                @endif
                @if (($return['admin_notes'] ?? '') !== '')
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('admin.returns.reject_reason') }}</dt>
                        <dd class="text-right text-foreground">{{ $return['admin_notes'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.returns.refund') }}</h2>
            <dl class="mt-3 flex flex-col gap-2 text-[13px]">
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.amount') }}</dt>
                    <dd class="text-foreground">{{ \App\Support\AdminStore::money($return['amount']) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.refund') }}</dt>
                    <dd class="text-foreground">{{ \App\Support\AdminStore::money($return['refund']) }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('admin.returns.status') }}</dt>
                    <dd><x-admin.badge group="status" :status="$return['status']" /></dd>
                </div>
            </dl>
        </section>
    </div>

    @if (($return['status'] ?? '') === 'pending')
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <form method="POST" action="{{ route('admin.returns.approve', $return['id']) }}" class="admin-card rounded-2xl border p-4">
                @csrf
                <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.returns.approve') }}</h2>
                <p class="mt-2 text-[13px] text-muted-foreground">{{ __('admin.returns.approve_help') }}</p>
                <div class="mt-4">
                    <x-admin.button type="submit">{{ __('admin.returns.approve') }}</x-admin.button>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.returns.reject', $return['id']) }}" class="admin-card rounded-2xl border p-4">
                @csrf
                <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.returns.reject') }}</h2>
                <div class="mt-3">
                    <x-admin.field :label="__('admin.returns.reject_reason')" name="admin_notes" required>
                        <x-admin.textarea name="admin_notes" rows="3" required>{{ old('admin_notes') }}</x-admin.textarea>
                    </x-admin.field>
                </div>
                <div class="mt-4">
                    <x-admin.button type="submit" variant="danger">{{ __('admin.returns.reject') }}</x-admin.button>
                </div>
            </form>
        </div>
    @endif

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <section class="admin-card rounded-2xl border">
            <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.returns.stock') }}</h2>
            <ul class="flex flex-col gap-2 px-4 py-3 text-[13px]">
                @forelse ($return['stock_effects'] as $row)
                    <li class="flex justify-between gap-3">
                        <span class="text-foreground">{{ $row['product'] }}</span>
                        <span class="text-success">+{{ $row['qty'] }} · {{ $row['reference'] }}</span>
                    </li>
                @empty
                    <li class="text-muted-foreground">—</li>
                @endforelse
            </ul>
        </section>
        <section class="admin-card rounded-2xl border">
            <h2 class="border-b border-border px-4 py-3 text-sm font-medium text-foreground">{{ __('admin.returns.cash') }}</h2>
            <ul class="flex flex-col gap-2 px-4 py-3 text-[13px]">
                @forelse ($return['cash_effects'] as $row)
                    <li class="flex justify-between gap-3">
                        <span class="text-foreground">{{ $row['description'] }}</span>
                        <span class="text-destructive">{{ \App\Support\AdminStore::money($row['amount']) }}</span>
                    </li>
                @empty
                    <li class="text-muted-foreground">—</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
