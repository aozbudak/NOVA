function initNavGroups() {
    const storageKey = 'nova.admin.nav-groups';
    let saved = {};

    try {
        saved = JSON.parse(localStorage.getItem(storageKey) ?? '{}') || {};
    } catch {
        saved = {};
    }

    const persist = () => {
        localStorage.setItem(storageKey, JSON.stringify(saved));
    };

    document.querySelectorAll('[data-nav-group]').forEach((group) => {
        const key = group.dataset.navGroup;
        const toggle = group.querySelector('[data-nav-group-toggle]');

        if (! toggle || ! key) {
            return;
        }

        const items = group.querySelector('[data-nav-group-items]');

        const setOpen = (open) => {
            group.toggleAttribute('data-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            items?.classList.toggle('hidden', ! open);
            items?.classList.toggle('flex', open);
        };

        if (group.hasAttribute('data-active-group')) {
            setOpen(true);
        } else if (Object.hasOwn(saved, key)) {
            setOpen(Boolean(saved[key]));
        }

        toggle.addEventListener('click', () => {
            const open = ! group.hasAttribute('data-open');
            setOpen(open);
            saved[key] = open;
            persist();
        });
    });
}

function initSidebar() {
    const root = document.documentElement;
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    initNavGroups();

    if (! toggle) {
        return;
    }

    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

    const setCollapsed = (collapsed) => {
        root.classList.toggle('sidebar-collapsed', collapsed);
        localStorage.setItem('nova.admin.sidebar', collapsed ? 'collapsed' : 'expanded');
        toggle.setAttribute('aria-label', collapsed ? toggle.dataset.expandLabel ?? '' : toggle.dataset.collapseLabel ?? '');
    };

    const setOpen = (open) => {
        root.classList.toggle('sidebar-open', open);
        backdrop?.classList.toggle('hidden', ! open);
        document.body.classList.toggle('overflow-hidden', open);
    };

    toggle.addEventListener('click', () => {
        if (isDesktop()) {
            setCollapsed(! root.classList.contains('sidebar-collapsed'));
            return;
        }

        setOpen(! root.classList.contains('sidebar-open'));
    });

    backdrop?.addEventListener('click', () => setOpen(false));

    window.addEventListener('resize', () => {
        if (isDesktop()) {
            setOpen(false);
        }
    });
}

function initDropdowns() {
    const closeAll = (except = null) => {
        document.querySelectorAll('[data-dropdown]').forEach((root) => {
            if (root === except) {
                return;
            }

            root.querySelector('[data-dropdown-panel]')?.setAttribute('hidden', '');
            root.querySelector('[data-dropdown-trigger]')?.setAttribute('aria-expanded', 'false');
        });
    };

    document.querySelectorAll('[data-dropdown]').forEach((root) => {
        const trigger = root.querySelector('[data-dropdown-trigger]');
        const panel = root.querySelector('[data-dropdown-panel]');

        if (! trigger || ! panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const open = panel.hasAttribute('hidden');
            closeAll(root);
            panel.toggleAttribute('hidden', ! open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });
}

function initSearch() {
    const overlay = document.querySelector('[data-admin-search]');
    const input = document.querySelector('[data-admin-search-input]');
    const empty = document.querySelector('[data-admin-search-empty]');
    const pages = document.querySelector('[data-admin-search-pages]');
    const groups = document.querySelector('[data-admin-search-groups]');
    const items = [...document.querySelectorAll('[data-search-item]')];
    const labels = JSON.parse(overlay?.dataset.searchGroups ?? '{}');
    const url = overlay?.dataset.searchUrl || window.NOVA?.api?.search || '';
    let timer = 0;

    if (! overlay || ! input) {
        return;
    }

    const open = () => {
        overlay.hidden = false;
        input.focus();
        document.body.classList.add('overflow-hidden');
    };

    const reset = () => {
        items.forEach((item) => item.classList.remove('hidden'));
        pages?.removeAttribute('hidden');
        groups?.setAttribute('hidden', '');
        if (groups) {
            groups.innerHTML = '';
        }
        empty?.setAttribute('hidden', '');
    };

    const close = () => {
        overlay.hidden = true;
        input.value = '';
        reset();
        document.body.classList.remove('overflow-hidden');
    };

    const renderGroups = (payload) => {
        const sections = Object.entries(payload).filter(([, rows]) => rows.length > 0);

        if (sections.length === 0) {
            groups?.setAttribute('hidden', '');
            empty?.removeAttribute('hidden');
            return;
        }

        empty?.setAttribute('hidden', '');
        groups?.removeAttribute('hidden');
        groups.innerHTML = sections.map(([key, rows]) => `
            <p class="px-2.5 pt-2 pb-1 text-[10px] font-medium tracking-wide text-muted-foreground uppercase">${escapeHtml(labels[key] ?? key)}</p>
            ${rows.map((row) => `
                <a href="${escapeHtml(row.url)}" class="flex flex-col rounded-lg px-2.5 py-2 hover:bg-accent">
                    <span class="text-[13px] text-foreground">${escapeHtml(row.label)}</span>
                    <span class="text-[11px] text-muted-foreground">${escapeHtml(row.meta)}</span>
                </a>
            `).join('')}
        `).join('');
    };

    const queryRecords = (needle) => {
        if (needle === '' || url === '') {
            reset();
            return;
        }

        fetch(`${url}?q=${encodeURIComponent(needle)}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => {
                if (! response.ok) {
                    throw new Error('search');
                }

                return response.json();
            })
            .then((data) => {
                pages?.setAttribute('hidden', '');
                renderGroups(data.groups ?? {});
            })
            .catch(() => {
                pages?.setAttribute('hidden', '');
                empty?.removeAttribute('hidden');
                if (empty) {
                    empty.textContent = document.getElementById('admin-toast')?.dataset.errorFallback ?? '';
                }
                showAdminLoadError();
                showAdminToast(document.getElementById('admin-toast')?.dataset.errorFallback ?? 'Something went wrong. Please try again.');
            });
    };

    document.querySelectorAll('[data-open-search]').forEach((button) => {
        button.addEventListener('click', open);
    });

    overlay.querySelector('[data-close-search]')?.addEventListener('click', close);
    overlay.querySelector('[data-search-backdrop]')?.addEventListener('click', close);

    input.addEventListener('input', () => {
        const needle = input.value.trim();
        window.clearTimeout(timer);

        if (needle === '') {
            reset();
            return;
        }

        timer = window.setTimeout(() => queryRecords(needle), 180);
    });

    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            open();
        }

        if (event.key === 'Escape' && ! overlay.hidden) {
            close();
        }
    });
}

function showAdminToast(message, duration = 2200) {
    const root = document.getElementById('admin-toast');
    const text = root?.querySelector('[data-toast-message]');

    if (! root || ! text || ! message) {
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
    }, duration);
}

function initAdminToast() {
    const root = document.getElementById('admin-toast');

    if (! root) {
        return;
    }

    const status = (root.dataset.flashStatus ?? '').trim();
    const error = (root.dataset.flashError ?? '').trim();

    if (status !== '') {
        showAdminToast(status);
        return;
    }

    if (error !== '') {
        showAdminToast(root.dataset.errorFallback ?? error);
    }
}

function openAdminLayer(name) {
    const layer = document.querySelector(`[data-admin-layer="${name}"]`);

    if (! layer) {
        return;
    }

    layer.hidden = false;
    document.body.classList.add('overflow-hidden');
    layer.querySelector('input, button, textarea, select')?.focus();
}

function closeAdminLayer(name = null) {
    const layers = name
        ? [document.querySelector(`[data-admin-layer="${name}"]`)]
        : [...document.querySelectorAll('[data-admin-layer]')];

    layers.forEach((layer) => {
        if (layer) {
            layer.hidden = true;
        }
    });

    if (! document.querySelector('[data-admin-layer]:not([hidden])')) {
        document.body.classList.remove('overflow-hidden');
    }
}

function initAdminLayers() {
    document.addEventListener('click', (event) => {
        const open = event.target.closest('[data-open-layer]');
        if (open) {
            openAdminLayer(open.dataset.openLayer);
            return;
        }

        if (event.target.closest('[data-close-layer]') || event.target.closest('[data-layer-backdrop]')) {
            const layer = event.target.closest('[data-admin-layer]');
            closeAdminLayer(layer?.dataset.adminLayer ?? null);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAdminLayer();
        }
    });
}

function initConfirm() {
    const form = document.querySelector('[data-confirm-form]');
    const title = document.querySelector('[data-confirm-title]');
    const body = document.querySelector('[data-confirm-body]');
    const method = document.querySelector('[data-confirm-method]');
    let pendingForm = null;

    if (! form) {
        return;
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-confirm]');

        if (! trigger) {
            return;
        }

        event.preventDefault();
        title.textContent = trigger.dataset.confirmTitle ?? '';
        body.textContent = trigger.dataset.confirmBody ?? '';
        method.value = trigger.dataset.confirmMethod ?? 'POST';
        pendingForm = trigger.closest('form');

        if (trigger.dataset.confirmAction) {
            form.action = trigger.dataset.confirmAction;
            pendingForm = null;
        } else if (pendingForm) {
            form.action = pendingForm.action;
        }

        openAdminLayer('confirm');
    });

    form.addEventListener('submit', (event) => {
        if (! pendingForm) {
            return;
        }

        event.preventDefault();
        closeAdminLayer('confirm');
        if (pendingForm.dataset.confirmed === 'true') {
            return;
        }
        pendingForm.dataset.confirmed = 'true';
        pendingForm.requestSubmit();
    });
}

function initBusyForms() {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (! (form instanceof HTMLFormElement) || form.dataset.busy === 'true') {
            if (form.dataset.busy === 'true') {
                event.preventDefault();
            }
            return;
        }

        const button = form.querySelector('[data-busy-label], button[type="submit"]');

        if (! button) {
            return;
        }

        form.dataset.busy = 'true';
        button.disabled = true;
        if (button.dataset.busyLabel) {
            button.textContent = button.dataset.busyLabel;
        }
    });
}

function initTableLoading() {
    document.querySelectorAll('form[data-table-filter]').forEach((form) => {
        form.addEventListener('submit', () => {
            const shell = form.parentElement?.querySelector('[data-table-shell]') ?? document.querySelector('[data-table-shell]');
            shell?.querySelector('[data-table-skeleton]')?.removeAttribute('hidden');
            shell?.querySelector('[data-table-body]')?.setAttribute('hidden', '');
        });
    });

    document.querySelectorAll('a[href*="range="]').forEach((link) => {
        link.addEventListener('click', () => {
            const skeleton = document.querySelector('[data-dashboard-skeleton]');
            if (skeleton) {
                skeleton.hidden = false;
            }
        });
    });
}

function initAuditRows() {
    document.querySelectorAll('[data-audit-row]').forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                return;
            }

            row.nextElementSibling?.toggleAttribute('hidden');
        });
    });
}

initSidebar();
initDropdowns();
initSearch();
initPos();
initVariantRows();
initVatCalculator();
initStockAdjust();
initFilterForms();
initUserAbilities();
initAdminToast();
initAdminLayers();
initConfirm();
initBusyForms();
initTableLoading();
initAuditRows();
initAdminRetry();

function showAdminLoadError(shell = document.querySelector('[data-table-shell]')) {
    shell?.querySelector('[data-table-error]')?.removeAttribute('hidden');
    shell?.querySelector('[data-table-body]')?.setAttribute('hidden', '');
    shell?.querySelector('[data-table-skeleton]')?.setAttribute('hidden', '');
}

function initAdminRetry() {
    document.querySelectorAll('[data-admin-retry]').forEach((button) => {
        button.addEventListener('click', () => window.location.reload());
    });
}

function initUserAbilities() {
    const field = document.querySelector('[data-user-role]');
    const boxes = [...document.querySelectorAll('[data-user-ability]')];

    if (! field || boxes.length === 0) {
        return;
    }

    const defaults = JSON.parse(field.dataset.roleAbilities ?? '{}');

    field.addEventListener('change', () => {
        const typed = field.value.trim().toLowerCase();
        const key = Object.keys(defaults).find((name) => name.toLowerCase() === typed);

        if (key === undefined) {
            return;
        }

        const allowed = defaults[key];

        boxes.forEach((box) => {
            box.checked = allowed.includes(box.value);
        });
    });
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function money(value) {
    return '₺' + Math.round(value).toLocaleString('en-US');
}

function initVatCalculator() {
    document.querySelectorAll('[data-vat-calculator]').forEach((root) => {
        const grossInput = root.querySelector('[data-vat-gross]');
        const rateSelect = root.querySelector('[data-vat-rate]');
        const netEl = root.querySelector('[data-vat-net]');
        const vatEl = root.querySelector('[data-vat-amount]');
        const inclusiveEl = root.querySelector('[data-vat-inclusive]');

        const format = (cents) => {
            return (cents / 100).toLocaleString('tr-TR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }) + ' TL';
        };

        const recalc = () => {
            const cents = Math.round((Number(grossInput?.value) || 0) * 100);
            const rate = Number(rateSelect?.value) || 0;
            const netCents = rate === 0 ? cents : Math.round((cents * 100) / (100 + rate));
            const vatCents = cents - netCents;

            if (netEl) {
                netEl.textContent = format(netCents);
            }

            if (vatEl) {
                vatEl.textContent = format(vatCents);
            }

            if (inclusiveEl) {
                inclusiveEl.textContent = format(cents);
            }
        };

        grossInput?.addEventListener('input', recalc);
        rateSelect?.addEventListener('change', recalc);
        recalc();
    });
}

function initStockAdjust() {
    document.querySelectorAll('[data-stock-form]').forEach((form) => {
        const product = form.querySelector('[data-stock-product]');
        const variant = form.querySelector('[data-stock-variant]');
        const type = form.querySelector('[data-stock-type]');
        const supplierField = form.querySelector('[data-stock-supplier]');

        if (! product || ! variant) {
            return;
        }

        const options = [...variant.querySelectorAll('option')];

        const sync = () => {
            const slug = product.value;

            options.forEach((option) => {
                if (option.value === '') {
                    option.hidden = false;

                    return;
                }

                const match = slug === '' || option.dataset.product === slug;
                option.hidden = ! match;

                if (! match && option.selected) {
                    variant.value = '';
                }
            });
        };

        const syncSupplier = () => {
            if (! supplierField) {
                return;
            }

            const inbound = ! type || type.value === 'in';
            const select = supplierField.querySelector('select');

            supplierField.hidden = ! inbound;

            if (select) {
                select.disabled = ! inbound;

                if (! inbound) {
                    select.value = '';
                }
            }
        };

        product.addEventListener('change', sync);
        type?.addEventListener('change', syncSupplier);
        sync();
        syncSupplier();
    });
}

function initFilterForms() {
    document.querySelectorAll('form[method="GET"] select, form[method="GET"] input[type="date"]').forEach((field) => {
        field.addEventListener('change', () => field.form?.requestSubmit());
    });
}

function initVariantRows() {
    const add = document.querySelector('[data-variant-add]');
    const list = document.querySelector('[data-variant-list]');
    const template = document.querySelector('[data-variant-template]');

    if (! add || ! list || ! template) {
        return;
    }

    add.addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', template.innerHTML);
    });
}

function initPos() {
    const root = document.querySelector('[data-pos]');

    if (! root) {
        return;
    }

    const search = root.querySelector('[data-pos-search]');
    const resultsList = root.querySelector('[data-pos-results]');
    const empty = root.querySelector('[data-pos-empty]');
    const body = root.querySelector('[data-pos-cart-body]');
    const table = root.querySelector('[data-pos-cart-table]');
    const cartEmpty = root.querySelector('[data-pos-cart-empty]');
    const subtotalEl = root.querySelector('[data-pos-subtotal]');
    const discountEl = root.querySelector('[data-pos-discount]');
    const totalEl = root.querySelector('[data-pos-total]');
    const otherNote = root.querySelector('[data-pos-other-note]');
    const noteInput = root.querySelector('[data-pos-note]');
    const lastSale = root.querySelector('[data-pos-last-sale]');
    const lastSaleNumber = root.querySelector('[data-pos-last-sale-number]');
    /** @type {HTMLElement[]} */
    let items = [...root.querySelectorAll('[data-pos-item]')];
    /** @type {Map<string, {sku: string, name: string, brand: string, variant: string, price: number, qty: number, discount: number, stock: number, barcode: string}>} */
    const cart = new Map();
    let searchTimer = 0;
    let paying = false;

    const refreshItemNodes = () => {
        items = [...root.querySelectorAll('[data-pos-item]')];
    };

    const validationMessage = (payload, fallback) => {
        const errors = payload?.errors;

        if (errors && typeof errors === 'object') {
            const first = Object.values(errors)[0];

            if (Array.isArray(first) && first[0]) {
                return String(first[0]);
            }
        }

        return payload?.message ?? fallback;
    };

    const renderItems = (rows) => {
        if (! resultsList || ! Array.isArray(rows)) {
            return;
        }

        const stockLabel = resultsList.dataset.stockLabel ?? '';

        resultsList.innerHTML = rows.map((item) => `
            <li>
                <button
                    type="button"
                    data-pos-item
                    data-sku="${escapeHtml(item.sku)}"
                    data-barcode="${escapeHtml(item.barcode ?? '')}"
                    data-name="${escapeHtml(item.name)}"
                    data-brand="${escapeHtml(item.brand ?? '')}"
                    data-variant="${escapeHtml(item.variant)}"
                    data-price="${escapeHtml(item.price)}"
                    data-stock="${escapeHtml(item.stock)}"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left hover:bg-accent"
                >
                    ${item.image
                        ? `<img src="${escapeHtml(item.image)}" alt="" width="36" height="44" class="h-11 w-9 object-cover">`
                        : `<span class="inline-block h-11 w-9 bg-muted"></span>`
                    }
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[13px] text-foreground">${escapeHtml(item.name)}</span>
                        <span class="block truncate text-[12px] text-muted-foreground">${escapeHtml([item.brand, item.variant, item.sku, item.barcode].filter(Boolean).join(' · '))}</span>
                    </span>
                    <span class="shrink-0 text-right">
                        <span class="block text-[13px] text-foreground">${money(item.price)}</span>
                        <span class="block text-[11px] text-muted-foreground">${escapeHtml(stockLabel)} ${escapeHtml(item.stock)}</span>
                    </span>
                </button>
            </li>
        `).join('');

        refreshItemNodes();
        empty?.toggleAttribute('hidden', rows.length > 0);
    };

    const loadItems = async (query = '') => {
        const url = window.NOVA?.api?.posItems;

        if (! url) {
            return;
        }

        try {
            const endpoint = new URL(url, window.location.origin);

            if (query) {
                endpoint.searchParams.set('q', query);
            }

            const response = await fetch(endpoint.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (! response.ok) {
                return;
            }

            const payload = await response.json();
            renderItems(payload.data ?? []);
        } catch {
            return;
        }
    };

    const payload = (button) => ({
        sku: button.dataset.sku,
        name: button.dataset.name,
        brand: button.dataset.brand ?? '',
        variant: button.dataset.variant,
        price: Number(button.dataset.price),
        barcode: button.dataset.barcode,
        stock: Number(button.dataset.stock ?? 0),
    });

    const renderCart = () => {
        const lines = [...cart.values()];
        table?.classList.toggle('hidden', lines.length === 0);
        cartEmpty?.classList.toggle('hidden', lines.length > 0);

        if (! body) {
            return;
        }

        body.innerHTML = lines.map((line) => `
            <tr class="border-b border-border">
                <td class="px-3 py-2" data-label="${escapeHtml(table?.dataset.labelProduct ?? '')}">
                    <span class="block text-foreground">${escapeHtml(line.name)}</span>
                    <span class="block text-muted-foreground">${escapeHtml(line.variant)}</span>
                    <span class="block text-muted-foreground">${escapeHtml(line.sku)}</span>
                </td>
                <td class="px-3 py-2" data-label="${escapeHtml(table?.dataset.labelQty ?? '')}">
                    <button type="button" data-pos-qty="${escapeHtml(line.sku)}" data-delta="-1" class="px-1 text-muted-foreground">−</button>
                    ${line.qty}
                    <button type="button" data-pos-qty="${escapeHtml(line.sku)}" data-delta="1" class="px-1 text-muted-foreground">+</button>
                </td>
                <td class="px-3 py-2 text-foreground" data-label="${escapeHtml(table?.dataset.labelUnit ?? '')}">${money(line.price)}</td>
                <td class="px-3 py-2 text-muted-foreground" data-label="${escapeHtml(table?.dataset.labelDiscount ?? '')}">
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        value="${line.discount}"
                        data-pos-discount-input="${escapeHtml(line.sku)}"
                        class="h-7 w-16 rounded-md border border-input bg-background px-1 text-[12px] text-foreground"
                    >
                </td>
                <td class="px-3 py-2 text-foreground" data-label="${escapeHtml(table?.dataset.labelTotal ?? '')}">
                    <span class="mr-2">${money((line.price * line.qty) - line.discount)}</span>
                    <button type="button" data-pos-remove="${escapeHtml(line.sku)}" class="text-muted-foreground hover:text-foreground">${escapeHtml(table?.dataset.labelRemove ?? '×')}</button>
                </td>
            </tr>
        `).join('');

        const subtotal = lines.reduce((sum, line) => sum + (line.price * line.qty), 0);
        const discount = lines.reduce((sum, line) => sum + line.discount, 0);

        if (subtotalEl) {
            subtotalEl.textContent = money(subtotal);
        }
        if (discountEl) {
            discountEl.textContent = money(discount);
        }
        if (totalEl) {
            totalEl.textContent = money(subtotal - discount);
        }
    };

    const addItem = (data) => {
        const current = cart.get(data.sku);
        const nextQty = (current?.qty ?? 0) + 1;

        if (Number.isFinite(data.stock) && data.stock >= 0 && nextQty > data.stock) {
            showAdminToast(root.dataset.posInsufficient ?? '');
            return;
        }

        if (current) {
            current.qty = nextQty;
            current.stock = data.stock;
        } else {
            cart.set(data.sku, { ...data, qty: 1, discount: 0 });
        }

        renderCart();
    };

    const hideOtherNote = () => {
        if (otherNote) {
            otherNote.hidden = true;
        }

        if (noteInput) {
            noteInput.value = '';
        }
    };

    const showOtherNote = () => {
        if (cart.size === 0 || paying) {
            return;
        }

        if (otherNote) {
            otherNote.hidden = false;
        }

        noteInput?.focus();
    };

    const rememberSaleNumber = (number) => {
        if (! lastSale || ! lastSaleNumber || ! number) {
            return;
        }

        lastSaleNumber.textContent = number;
        lastSale.hidden = false;
    };

    const checkout = async (payment) => {
        if (cart.size === 0 || paying) {
            return;
        }

        const note = payment === 'other' ? (noteInput?.value ?? '').trim() : '';

        if (payment === 'other' && note === '') {
            showOtherNote();
            showAdminToast(root.dataset.posNoteRequired ?? '');
            return;
        }

        for (const line of cart.values()) {
            if (line.discount > (line.price * line.qty)) {
                showAdminToast(root.dataset.posDiscountError ?? '');
                return;
            }

            if (Number.isFinite(line.stock) && line.stock >= 0 && line.qty > line.stock) {
                showAdminToast(root.dataset.posInsufficient ?? '');
                return;
            }
        }

        paying = true;

        try {
            const response = await fetch(window.NOVA?.api?.sales ?? '/api/sales', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.NOVA?.csrf ?? document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    payment,
                    note: note === '' ? null : note,
                    items: [...cart.values()].map((line) => ({
                        sku: line.sku,
                        quantity: line.qty,
                        discount: line.discount,
                    })),
                }),
            });

            const data = await response.json().catch(() => ({}));

            if (! response.ok) {
                showAdminToast(validationMessage(data, document.getElementById('admin-toast')?.dataset.errorFallback ?? 'Something went wrong. Please try again.'));
                return;
            }

            const paymentLabels = {
                cash: root.dataset.posCashLabel,
                card: root.dataset.posCardLabel,
                other: root.dataset.posOtherLabel,
            };
            const sale = data.data ?? {};
            const summary = [
                data.message ?? '',
                `${root.dataset.posSaleNoLabel ?? ''}: ${sale.number ?? ''}`.trim(),
                `${root.dataset.posTotalLabel ?? ''}: ${money(sale.total ?? 0)}`.trim(),
                `${root.dataset.posPaymentLabel ?? ''}: ${paymentLabels[sale.payment] ?? sale.payment ?? ''}`.trim(),
            ].filter(Boolean).join('\n');

            cart.clear();
            hideOtherNote();
            rememberSaleNumber(sale.number ?? '');
            renderCart();
            await loadItems(search?.value?.trim() ?? '');
            showAdminToast(summary, 5000);
        } catch {
            showAdminToast(document.getElementById('admin-toast')?.dataset.errorFallback ?? 'Something went wrong. Please try again.');
        } finally {
            paying = false;
        }
    };

    const visibleItems = () => items.filter((item) => ! item.closest('li')?.classList.contains('hidden'));

    const filter = (needle) => {
        const value = needle.trim().toLowerCase();
        let shown = 0;

        items.forEach((item) => {
            const haystack = `${item.dataset.sku} ${item.dataset.barcode} ${item.dataset.name} ${item.dataset.brand} ${item.dataset.variant}`.toLowerCase();
            const match = value === '' || haystack.includes(value);
            item.closest('li')?.classList.toggle('hidden', ! match);
            if (match) {
                shown += 1;
            }
        });

        empty?.toggleAttribute('hidden', shown > 0);
    };

    search?.addEventListener('input', () => {
        filter(search.value);
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => {
            loadItems(search.value.trim());
        }, 150);
    });

    search?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        const needle = search.value.trim().toLowerCase();
        const exact = items.find((item) => item.dataset.barcode?.toLowerCase() === needle || item.dataset.sku?.toLowerCase() === needle);
        const first = exact ?? visibleItems()[0];

        if (first) {
            addItem(payload(first));
            search.value = '';
            filter('');
            loadItems();
        }
    });

    root.addEventListener('click', (event) => {
        const item = event.target.closest('[data-pos-item]');
        if (item) {
            addItem(payload(item));
            search?.focus();
            return;
        }

        const qty = event.target.closest('[data-pos-qty]');
        if (qty) {
            const line = cart.get(qty.dataset.posQty);
            if (! line) {
                return;
            }
            const nextQty = line.qty + Number(qty.dataset.delta);
            if (Number(qty.dataset.delta) > 0 && Number.isFinite(line.stock) && nextQty > line.stock) {
                showAdminToast(root.dataset.posInsufficient ?? '');
                return;
            }
            line.qty = nextQty;
            if (line.qty <= 0) {
                cart.delete(line.sku);
            }
            renderCart();
            return;
        }

        const remove = event.target.closest('[data-pos-remove]');
        if (remove) {
            cart.delete(remove.dataset.posRemove);
            renderCart();
            return;
        }

        const clear = event.target.closest('[data-pos-clear]');
        if (clear) {
            cart.clear();
            hideOtherNote();
            renderCart();
            return;
        }

        const pay = event.target.closest('[data-pos-pay]');
        if (pay) {
            event.preventDefault();

            if (pay.dataset.posPay === 'other') {
                showOtherNote();
                return;
            }

            hideOtherNote();
            checkout(pay.dataset.posPay);
            return;
        }

        if (event.target.closest('[data-pos-note-cancel]')) {
            hideOtherNote();
            return;
        }

        if (event.target.closest('[data-pos-note-confirm]')) {
            checkout('other');
            return;
        }

        const copySale = event.target.closest('[data-pos-copy-sale]');

        if (copySale && lastSaleNumber?.textContent) {
            navigator.clipboard?.writeText(lastSaleNumber.textContent).then(() => {
                showAdminToast(root.dataset.posCopied ?? lastSaleNumber.textContent);
            }).catch(() => {
                showAdminToast(lastSaleNumber.textContent);
            });
        }
    });

    root.addEventListener('change', (event) => {
        const input = event.target.closest('[data-pos-discount-input]');

        if (! input) {
            return;
        }

        const line = cart.get(input.dataset.posDiscountInput);

        if (! line) {
            return;
        }

        const value = Math.max(0, Number(input.value) || 0);
        const max = line.price * line.qty;

        if (value > max) {
            showAdminToast(root.dataset.posDiscountError ?? '');
            input.value = String(line.discount);
            return;
        }

        line.discount = value;
        renderCart();
    });

    document.addEventListener('keydown', (event) => {
        if (! root.isConnected) {
            return;
        }

        if (event.key === 'F2' || event.key === 'F3' || event.key === 'F4') {
            event.preventDefault();

            if (event.key === 'F4') {
                showOtherNote();
                return;
            }

            hideOtherNote();
            checkout({ F2: 'cash', F3: 'card' }[event.key]);
            return;
        }

        if (event.key === 'Enter' && event.target === noteInput) {
            event.preventDefault();
            checkout('other');
            return;
        }

        if (event.target instanceof HTMLInputElement && event.target !== search) {
            return;
        }

        if (event.key === '/' && document.activeElement !== search) {
            event.preventDefault();
            search?.focus();
        }
    });

    search?.focus();
    loadItems();
}
