@props([
    'label' => '',
    'align' => 'start',
    'tone' => 'default',
])

<td
    data-label="{{ $label }}"
    {{ $attributes->class([
        'px-3 py-2.5',
        'text-right' => $align === 'end',
        'text-center' => $align === 'center',
        'text-foreground' => $tone === 'default',
        'text-muted-foreground' => $tone === 'muted',
        'text-success' => $tone === 'success',
        'text-destructive' => $tone === 'danger',
    ]) }}
>
    {{ $slot }}
</td>
