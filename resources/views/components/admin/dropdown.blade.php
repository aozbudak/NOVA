@props([
    'align' => 'right',
])

<div {{ $attributes->class('relative') }} data-dropdown>
    <div data-dropdown-trigger>
        {{ $trigger }}
    </div>
    <div
        data-dropdown-panel
        hidden
        @class([
            'absolute z-30 mt-1 min-w-36 rounded-md border border-border bg-card py-1 shadow-sm',
            'right-0' => $align === 'right',
            'left-0' => $align === 'left',
        ])
    >
        {{ $slot }}
    </div>
</div>
