<footer class="mt-24 border-t border-border">
    <div class="mx-auto grid max-w-[1600px] gap-10 px-4 py-16 md:grid-cols-4 md:px-8">
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">Shop</p>
            <ul class="mt-4 flex flex-col gap-2 text-sm text-muted-foreground">
                <li><a href="{{ route('shop.show', 'women') }}" class="hover:text-foreground">Women</a></li>
                <li><a href="{{ route('shop.show', 'men') }}" class="hover:text-foreground">Men</a></li>
                <li><a href="{{ route('shop.show', 'new-in') }}" class="hover:text-foreground">New In</a></li>
                <li><a href="{{ route('shop.show', 'collections') }}" class="hover:text-foreground">Collections</a></li>
                <li><a href="{{ route('shop.show', 'sale') }}" class="hover:text-foreground">Sale</a></li>
            </ul>
        </div>
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">Help</p>
            <ul class="mt-4 flex flex-col gap-2 text-sm text-muted-foreground">
                <li><a href="{{ route('pages.show', 'contact') }}" class="hover:text-foreground">Contact</a></li>
                <li><a href="{{ route('pages.show', 'shipping') }}" class="hover:text-foreground">Shipping</a></li>
                <li><a href="{{ route('pages.show', 'returns') }}" class="hover:text-foreground">Returns</a></li>
                <li><a href="{{ route('pages.show', 'faq') }}" class="hover:text-foreground">FAQ</a></li>
                <li><a href="{{ route('pages.show', 'size-guide') }}" class="hover:text-foreground">Size Guide</a></li>
            </ul>
        </div>
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">About NOVA</p>
            <ul class="mt-4 flex flex-col gap-2 text-sm text-muted-foreground">
                <li><a href="{{ route('pages.show', 'about') }}" class="hover:text-foreground">About</a></li>
                <li><a href="{{ route('pages.show', 'careers') }}" class="hover:text-foreground">Careers</a></li>
                <li><a href="{{ route('pages.show', 'sustainability') }}" class="hover:text-foreground">Sustainability</a></li>
            </ul>
        </div>
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">Legal</p>
            <ul class="mt-4 flex flex-col gap-2 text-sm text-muted-foreground">
                <li><a href="{{ route('pages.show', 'privacy') }}" class="hover:text-foreground">Privacy</a></li>
                <li><a href="{{ route('pages.show', 'terms') }}" class="hover:text-foreground">Terms</a></li>
                <li><a href="{{ route('pages.show', 'cookies') }}" class="hover:text-foreground">Cookies</a></li>
            </ul>
            <div class="mt-8 flex gap-4">
                <a href="https://instagram.com" class="text-foreground" aria-label="Instagram" rel="noopener noreferrer">
                    <x-icon name="instagram" />
                </a>
                <a href="https://pinterest.com" class="text-foreground" aria-label="Pinterest" rel="noopener noreferrer">
                    <x-icon name="pinterest" />
                </a>
                <a href="https://x.com" class="text-foreground" aria-label="X" rel="noopener noreferrer">
                    <x-icon name="x-social" />
                </a>
            </div>
        </div>
    </div>
    <div class="mx-auto flex max-w-[1600px] flex-col items-start justify-between gap-3 px-4 py-8 text-xs text-muted-foreground md:flex-row md:items-center md:px-8">
        <x-logo size="text-[11px]" class="text-muted-foreground" />
        <p>© 2026 NOVA</p>
    </div>
</footer>
