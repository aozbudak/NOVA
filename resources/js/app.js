const nova = () => window.NOVA ?? {};

const headers = (json = true) => ({
    Accept: json ? 'application/json' : 'text/html',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': nova().csrf,
    'X-Requested-With': 'XMLHttpRequest',
});

function toast(message) {
    const root = document.getElementById('toast');
    const text = root?.querySelector('[data-toast-message]');

    if (! root || ! text) {
        return;
    }

    text.textContent = message;
    root.hidden = false;
    requestAnimationFrame(() => root.classList.remove('opacity-0'));
    window.clearTimeout(root._timer);
    root._timer = window.setTimeout(() => {
        root.classList.add('opacity-0');
        window.setTimeout(() => {
            root.hidden = true;
        }, 200);
    }, 1800);
}

function setCartCount(count) {
    document.body.dataset.cartCount = String(count);
    document.querySelectorAll('[data-cart-badge]').forEach((badge) => {
        badge.textContent = String(count);
        badge.classList.toggle('hidden', count < 1);
    });
}

async function refreshCartPanel() {
    const panel = document.querySelector('[data-cart-panel]');

    if (! panel) {
        return;
    }

    const response = await fetch(nova().routes.cartPanel, {
        headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
    });

    panel.innerHTML = await response.text();
}

function openLayer(name) {
    const drawer = document.querySelector(`[data-drawer="${name}"]`);
    const overlay = document.querySelector(`[data-overlay="${name}"]`);
    const modal = document.querySelector(`[data-modal="${name}"]`);
    const root = drawer ?? overlay ?? modal;

    if (! root) {
        return;
    }

    root.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
        root.querySelector('[data-drawer-backdrop]')?.classList.add('opacity-100');
        root.querySelector('[data-drawer-panel]')?.classList.remove('translate-x-full', '-translate-x-full');
    });

    const focusTarget = root.querySelector('input, button');
    focusTarget?.focus();
}

function closeLayer(name) {
    const root = document.querySelector(`[data-drawer="${name}"], [data-overlay="${name}"], [data-modal="${name}"]`);

    if (! root) {
        return;
    }

    root.querySelector('[data-drawer-backdrop]')?.classList.remove('opacity-100');
    const panel = root.querySelector('[data-drawer-panel]');

    if (panel) {
        panel.classList.add(panel.classList.contains('right-0') || panel.className.includes('right-0') ? 'translate-x-full' : '-translate-x-full');
    }

    window.setTimeout(() => {
        root.classList.add('hidden');
        if (! document.querySelector('[data-drawer]:not(.hidden), [data-overlay]:not(.hidden), [data-modal]:not(.hidden)')) {
            document.body.classList.remove('overflow-hidden');
        }
    }, 180);
}

function closeAllLayers() {
    document.querySelectorAll('[data-drawer], [data-overlay], [data-modal]').forEach((node) => {
        const name = node.dataset.drawer || node.dataset.overlay || node.dataset.modal;
        if (name && ! node.classList.contains('hidden')) {
            closeLayer(name);
        }
    });
}

async function addToCart(productId, size, quantity = 1) {
    const response = await fetch(nova().routes.cart, {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({ product_id: Number(productId), size, quantity }),
    });

    if (! response.ok) {
        toast('Something went wrong. Please try again.');
        return;
    }

    const data = await response.json();
    setCartCount(data.count);
    await refreshCartPanel();
    toast(data.message ?? 'Added to bag');
}

async function toggleWishlist(button) {
    const productId = Number(button.dataset.productId);
    const response = await fetch(nova().routes.wishlist, {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({ product_id: productId }),
    });

    if (! response.ok) {
        toast('Something went wrong. Please try again.');
        return;
    }

    const data = await response.json();
    const added = Boolean(data.added);

    document.querySelectorAll(`[data-wishlist-toggle][data-product-id="${productId}"]`).forEach((node) => {
        node.setAttribute('aria-pressed', added ? 'true' : 'false');
        node.setAttribute('aria-label', added ? 'Remove from wishlist' : 'Add to wishlist');
        const icon = node.querySelector('svg');
        if (icon) {
            icon.setAttribute('fill', added ? 'currentColor' : 'none');
        }
        node.classList.add('scale-110');
        window.setTimeout(() => node.classList.remove('scale-110'), 160);
    });
}

function renderSearchResults(query) {
    const results = document.querySelector('[data-search-results]');
    const trending = document.querySelector('[data-search-trending]');
    const catalog = nova().catalog ?? [];
    const needle = query.trim().toLowerCase();

    if (! results || ! trending) {
        return;
    }

    if (needle.length < 2) {
        results.classList.add('hidden');
        trending.classList.remove('hidden');
        return;
    }

    const matches = catalog.filter((item) => item.name.toLowerCase().includes(needle) || item.category.toLowerCase().includes(needle)).slice(0, 8);
    const formatter = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' });

    trending.classList.add('hidden');
    results.classList.remove('hidden');
    results.innerHTML = matches.length === 0
        ? '<p class="text-sm text-muted-foreground">No results</p>'
        : `<ul class="flex flex-col gap-4">${matches.map((item) => `
            <li>
                <a href="${nova().routes.product}/${item.slug}" class="flex items-center gap-4">
                    <img src="${item.image}" alt="${item.name}" width="56" height="70" class="h-[70px] w-14 object-cover" loading="lazy">
                    <span>
                        <span class="block text-sm">${item.name}</span>
                        <span class="block text-xs text-muted-foreground">${formatter.format(item.price)}</span>
                    </span>
                </a>
            </li>`).join('')}</ul>`;
}

function initTheme() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            const next = isDark ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            document.documentElement.classList.toggle('dark', next === 'dark');
            document.documentElement.classList.toggle('light', next === 'light');
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        if (localStorage.getItem('theme')) {
            return;
        }

        document.documentElement.classList.toggle('dark', event.matches);
        document.documentElement.classList.remove('light');
    });
}

function initHeader() {
    const header = document.querySelector('[data-header]');

    if (! header) {
        return;
    }

    const onScroll = () => {
        const stuck = window.scrollY > 8;
        header.classList.toggle('bg-background/0', ! stuck);
        header.classList.toggle('bg-background/90', stuck);
        header.classList.toggle('backdrop-blur-sm', stuck);
    };

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function initGallery() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
        const track = gallery.querySelector('[data-gallery-track]');

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const index = Number(thumb.dataset.galleryThumb);
                thumbs.forEach((item) => item.classList.remove('border-foreground'));
                thumb.classList.add('border-foreground');
                const slide = track?.children[index];
                slide?.scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
            });
        });
    });
}

let galleryImages = [];
let galleryIndex = 0;

function openImageViewer(images, index) {
    galleryImages = images;
    galleryIndex = index;
    const img = document.querySelector('[data-modal-image]');

    if (img) {
        img.src = galleryImages[galleryIndex];
        img.alt = 'Product image';
    }

    openLayer('image');
}

function stepGallery(delta) {
    if (galleryImages.length === 0) {
        return;
    }

    galleryIndex = (galleryIndex + delta + galleryImages.length) % galleryImages.length;
    const img = document.querySelector('[data-modal-image]');

    if (img) {
        img.src = galleryImages[galleryIndex];
    }
}

document.addEventListener('click', async (event) => {
    const open = event.target.closest('[data-open]');
    if (open) {
        if (open.dataset.open === 'cart') {
            await refreshCartPanel();
        }
        openLayer(open.dataset.open);
        return;
    }

    const close = event.target.closest('[data-close]');
    if (close) {
        closeLayer(close.dataset.close);
        return;
    }

    if (event.target.closest('[data-drawer-backdrop]')) {
        const drawer = event.target.closest('[data-drawer]');
        if (drawer) {
            closeLayer(drawer.dataset.drawer);
        }
        return;
    }

    const quickAdd = event.target.closest('[data-quick-add]');
    if (quickAdd) {
        event.preventDefault();
        await addToCart(quickAdd.dataset.productId, quickAdd.dataset.size);
        openLayer('cart');
        return;
    }

    const wishlist = event.target.closest('[data-wishlist-toggle]');
    if (wishlist) {
        event.preventDefault();
        await toggleWishlist(wishlist);
        return;
    }

    const qty = event.target.closest('[data-cart-qty]');
    if (qty) {
        const quantity = Number(qty.dataset.qty);
        await fetch(`${nova().routes.cartUpdate}/${encodeURIComponent(qty.dataset.cartQty)}`, {
            method: 'PATCH',
            headers: headers(),
            body: JSON.stringify({ quantity }),
        }).then(async (response) => {
            if (! response.ok) {
                toast('Something went wrong. Please try again.');
                return;
            }
            const data = await response.json();
            setCartCount(data.count);
            await refreshCartPanel();
        });
        return;
    }

    const remove = event.target.closest('[data-cart-remove]');
    if (remove) {
        await fetch(`${nova().routes.cartUpdate}/${encodeURIComponent(remove.dataset.cartRemove)}`, {
            method: 'DELETE',
            headers: headers(),
        }).then(async (response) => {
            if (! response.ok) {
                toast('Something went wrong. Please try again.');
                return;
            }
            const data = await response.json();
            setCartCount(data.count);
            await refreshCartPanel();
        });
        return;
    }

    const galleryOpen = event.target.closest('[data-gallery-open]');
    if (galleryOpen) {
        const gallery = galleryOpen.closest('[data-gallery]');
        const images = [...gallery.querySelectorAll('[data-gallery-track] img')].map((img) => img.src);
        openImageViewer(images, Number(galleryOpen.dataset.galleryOpen));
    }

    if (event.target.closest('[data-gallery-prev]')) {
        stepGallery(-1);
    }

    if (event.target.closest('[data-gallery-next]')) {
        stepGallery(1);
    }
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-add-to-cart]');

    if (! form) {
        return;
    }

    event.preventDefault();
    const data = new FormData(form);
    await addToCart(data.get('product_id'), data.get('size'), Number(data.get('quantity') ?? 1));
    openLayer('cart');
});

document.addEventListener('input', (event) => {
    if (event.target.matches('[data-search-input]')) {
        renderSearchResults(event.target.value);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeAllLayers();
    }

    if (event.key === 'ArrowLeft') {
        stepGallery(-1);
    }

    if (event.key === 'ArrowRight') {
        stepGallery(1);
    }
});

initTheme();
initHeader();
initGallery();
