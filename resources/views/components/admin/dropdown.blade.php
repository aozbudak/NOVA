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
            'admin-popover absolute z-30 mt-2 min-w-44 overflow-hidden rounded-2xl border py-1.5',
            'right-0' => $align === 'right',
            'left-0' => $align === 'left',
        ])
    >
        {{ $slot }}
    </div>
</div>
