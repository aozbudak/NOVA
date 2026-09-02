@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" class="min-w-0">
    <ol class="flex items-center gap-2 text-[13px] text-muted-foreground">
        @foreach ($items as $item)
            @if (! $loop->last && $item['url'])
                <li class="min-w-0 truncate">
                    <a href="{{ $item['url'] }}" class="hover:text-foreground">{{ $item['label'] }}</a>
                </li>
                <li aria-hidden="true" class="text-border">/</li>
            @else
                <li class="min-w-0 truncate text-foreground">{{ $item['label'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
