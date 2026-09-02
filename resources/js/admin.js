function initSidebar() {
    const root = document.documentElement;
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

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
    const url = overlay?.dataset.searchUrl ?? '';
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
            <p class="px-3 pt-2 pb-1 text-[10px] font-medium tracking-wide text-muted-foreground uppercase">${escapeHtml(labels[key] ?? key)}</p>
            ${rows.map((row) => `
                <a href="${escapeHtml(row.url)}" class="flex flex-col px-3 py-2 hover:bg-accent">
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

function showAdminToast(message) {
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
    }, 2200);
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
    const items = [...root.querySelectorAll('[data-pos-item]')];
    const empty = root.querySelector('[data-pos-empty]');
    const body = root.querySelector('[data-pos-cart-body]');
    const table = root.querySelector('[data-pos-cart-table]');
    const cartEmpty = root.querySelector('[data-pos-cart-empty]');
    const subtotalEl = root.querySelector('[data-pos-subtotal]');
    const discountEl = root.querySelector('[data-pos-discount]');
    const totalEl = root.querySelector('[data-pos-total]');
    /** @type {Map<string, {sku: string, name: string, variant: string, price: number, qty: number, discount: number}>} */
    const cart = new Map();

    const payload = (button) => ({
        sku: button.dataset.sku,
        name: button.dataset.name,
        variant: button.dataset.variant,
        price: Number(button.dataset.price),
        barcode: button.dataset.barcode,
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
                </td>
                <td class="px-3 py-2" data-label="${escapeHtml(table?.dataset.labelQty ?? '')}">
                    <button type="button" data-pos-qty="${escapeHtml(line.sku)}" data-delta="-1" class="px-1 text-muted-foreground">−</button>
                    ${line.qty}
                    <button type="button" data-pos-qty="${escapeHtml(line.sku)}" data-delta="1" class="px-1 text-muted-foreground">+</button>
                </td>
                <td class="px-3 py-2 text-foreground" data-label="${escapeHtml(table?.dataset.labelUnit ?? '')}">${money(line.price)}</td>
                <td class="px-3 py-2 text-muted-foreground" data-label="${escapeHtml(table?.dataset.labelDiscount ?? '')}">${money(line.discount)}</td>
                <td class="px-3 py-2 text-foreground" data-label="${escapeHtml(table?.dataset.labelTotal ?? '')}">${money((line.price * line.qty) - line.discount)}</td>
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

        if (current) {
            current.qty += 1;
        } else {
            cart.set(data.sku, { ...data, qty: 1, discount: 0 });
        }

        renderCart();
    };

    const checkout = async (payment) => {
        if (cart.size === 0) {
            return;
        }

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
                items: [...cart.values()].map((line) => ({
                    sku: line.sku,
                    quantity: line.qty,
                    discount: line.discount,
                })),
            }),
        });

        if (! response.ok) {
            showAdminToast(document.getElementById('admin-toast')?.dataset.errorFallback ?? 'Something went wrong. Please try again.');
            return;
        }

        const data = await response.json();
        cart.clear();
        renderCart();
        showAdminToast(data.message ?? data.data?.number ?? 'OK');
    };

    const visibleItems = () => items.filter((item) => ! item.closest('li')?.classList.contains('hidden'));

    const filter = (needle) => {
        const value = needle.trim().toLowerCase();
        let shown = 0;

        items.forEach((item) => {
            const haystack = `${item.dataset.sku} ${item.dataset.barcode} ${item.dataset.name} ${item.dataset.variant}`.toLowerCase();
            const match = value === '' || haystack.includes(value);
            item.closest('li')?.classList.toggle('hidden', ! match);
            if (match) {
                shown += 1;
            }
        });

        empty?.toggleAttribute('hidden', shown > 0);
    };

    search?.addEventListener('input', () => filter(search.value));

    search?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        const needle = search.value.trim().toLowerCase();
        const exact = items.find((item) => item.dataset.barcode === needle || item.dataset.sku?.toLowerCase() === needle);
        const first = exact ?? visibleItems()[0];

        if (first) {
            addItem(payload(first));
            search.value = '';
            filter('');
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
            line.qty += Number(qty.dataset.delta);
            if (line.qty <= 0) {
                cart.delete(line.sku);
            }
            renderCart();
            return;
        }

        const pay = event.target.closest('[data-pos-pay]');
        if (pay) {
            event.preventDefault();
            checkout(pay.dataset.posPay);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (! root.isConnected) {
            return;
        }

        if (event.key === 'F2' || event.key === 'F3' || event.key === 'F4') {
            event.preventDefault();
            checkout({ F2: 'cash', F3: 'card', F4: 'other' }[event.key]);
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
}
