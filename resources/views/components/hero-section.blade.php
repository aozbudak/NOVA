<section class="relative min-h-[78vh] overflow-hidden bg-muted md:min-h-[88vh]">
    <img
        src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=2400&q=80"
        srcset="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=800&q=80 800w, https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1600&q=80 1600w, https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=2400&q=80 2400w"
        sizes="100vw"
        alt="NOVA Autumn Winter 2026 campaign"
        width="2400"
        height="1600"
        class="absolute inset-0 h-full w-full object-cover"
        fetchpriority="high"
    >
    <div class="absolute inset-0 bg-foreground/25"></div>
    <div class="relative mx-auto flex min-h-[78vh] max-w-[1600px] flex-col justify-end px-4 pb-12 md:min-h-[88vh] md:px-8 md:pb-16">
        <p class="text-[11px] font-medium tracking-nav text-primary-foreground uppercase">NOVA Autumn / Winter 2026</p>
        <h1 class="mt-3 max-w-xl font-serif text-5xl tracking-tight text-primary-foreground md:text-7xl">The new standard</h1>
        <div class="mt-8 flex flex-wrap gap-3">
            <x-button href="{{ route('shop.show', 'women') }}" variant="light">Shop women</x-button>
            <x-button href="{{ route('shop.show', 'men') }}" variant="light">Shop men</x-button>
        </div>
    </div>
</section>
