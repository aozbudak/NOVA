@props(['images', 'name'])

<div data-gallery class="flex flex-col gap-3 lg:flex-row">
    <div class="hidden w-16 shrink-0 flex-col gap-2 lg:flex">
        @foreach ($images as $index => $image)
            <button type="button" data-gallery-thumb="{{ $index }}" class="overflow-hidden border border-transparent {{ $index === 0 ? 'border-foreground' : '' }}" aria-label="View image {{ $index + 1 }}">
                <img src="{{ $image }}" alt="" width="80" height="100" class="aspect-[4/5] w-full object-cover" loading="lazy">
            </button>
        @endforeach
    </div>
    <div class="relative min-w-0 flex-1 overflow-hidden">
        <div class="flex snap-x snap-mandatory overflow-x-auto lg:overflow-hidden" data-gallery-track>
            @foreach ($images as $index => $image)
                <button type="button" class="w-full shrink-0 snap-center" data-gallery-open="{{ $index }}" aria-label="Open {{ $name }} image {{ $index + 1 }}">
                    <img
                        src="{{ $image }}"
                        alt="{{ $name }}"
                        width="1200"
                        height="1500"
                        class="aspect-[4/5] w-full object-cover"
                        @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif
                    >
                </button>
            @endforeach
        </div>
    </div>
</div>
