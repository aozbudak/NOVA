@props([
    'paginator' => null,
    'empty' => false,
])

<div data-table-shell class="relative">
    <div data-table-skeleton hidden>
        <x-admin.skeleton.table />
    </div>
    <div data-table-error hidden>
        <x-admin.card>
            <x-admin.error />
        </x-admin.card>
    </div>
    <div data-table-body>
        @if ($empty)
            <x-admin.card>
                {{ $slot }}
            </x-admin.card>
        @else
            <div class="overflow-x-auto rounded-md border border-border bg-card">
                <table data-admin-table {{ $attributes->class('w-full text-left text-[13px]') }}>
                    @isset($head)
                        <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                            <tr>{{ $head }}</tr>
                        </thead>
                    @endisset
                    <tbody>{{ $body ?? $slot }}</tbody>
                </table>
            </div>
            @if ($paginator)
                <x-admin.pagination :paginator="$paginator" />
            @endif
        @endif
    </div>
</div>
