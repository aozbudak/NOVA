@props([
    'series' => [],
])

@php
    $values = collect($series)->pluck('value');
    $max = max(1, (float) $values->max());
    $count = max(1, $values->count());
    $width = 360;
    $height = 96;
    $points = $values->values()->map(function (int|float $value, int $index) use ($count, $max, $width, $height): string {
        $x = $count === 1 ? 0 : ($index / ($count - 1)) * $width;
        $y = $height - (($value / $max) * ($height - 16)) - 8;

        return $x.','.$y;
    })->implode(' ');
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2']) }}>
    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="h-24 w-full text-foreground" preserveAspectRatio="none" aria-hidden="true">
        <polyline fill="none" stroke="currentColor" stroke-width="1.5" points="{{ $points }}" />
    </svg>
    <div class="flex justify-between text-[11px] text-muted-foreground">
        @foreach ($series as $point)
            <span>{{ $point['label'] }}</span>
        @endforeach
    </div>
</div>
