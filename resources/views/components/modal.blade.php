<div data-modal="image" class="fixed inset-0 z-50 hidden bg-background" role="dialog" aria-modal="true" aria-label="Image viewer">
    <button type="button" data-close="image" class="absolute top-4 right-4 z-10 p-2" aria-label="Close image viewer">
        <x-icon name="x" />
    </button>
    <button type="button" data-gallery-prev class="absolute top-1/2 left-4 z-10 -translate-y-1/2 p-2" aria-label="Previous image">
        <x-icon name="chevron-left" />
    </button>
    <button type="button" data-gallery-next class="absolute top-1/2 right-4 z-10 -translate-y-1/2 p-2" aria-label="Next image">
        <x-icon name="chevron-right" />
    </button>
    <img data-modal-image src="" alt="" class="h-full w-full object-contain p-8 md:p-16">
</div>
