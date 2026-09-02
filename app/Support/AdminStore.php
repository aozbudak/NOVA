<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class AdminStore
{
    public static function money(float|int $amount): string
    {
        return '₺'.number_format($amount, 0, '.', ',');
    }

    /**
     * @return list<string>
     */
    public function categories(): array
    {
        return ['Shirts', 'Outerwear', 'Knitwear', 'Trousers', 'Dresses', 'Accessories'];
    }

    /**
     * @return list<string>
     */
    public function brands(): array
    {
        return ['NOVA', 'Atelier', 'Studio'];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function products(): Collection
    {
        return collect($this->catalog());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function product(string $slug): ?array
    {
        return $this->products()->firstWhere('slug', $slug);
    }

    /**
     * @param  array{search?: string|null, category?: string|null, brand?: string|null, status?: string|null, stock?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filteredProducts(array $filters): Collection
    {
        $products = $this->products();
        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $products = $products->filter(function (array $product) use ($search): bool {
                return Str::contains(Str::lower($product['name'].' '.$product['sku'].' '.$product['barcode']), $search);
            });
        }

        foreach (['category', 'brand', 'status'] as $key) {
            $value = $filters[$key] ?? null;

            if (filled($value)) {
                $products = $products->where($key, $value);
            }
        }

        if (filled($filters['stock'] ?? null)) {
            $products = $products->where('stock_status', $filters['stock']);
        }

        return $products->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function variants(): Collection
    {
        return $this->products()
            ->flatMap(function (array $product): Collection {
                return collect($product['variants'])->map(fn (array $variant): array => [
                    ...$variant,
                    'product' => $product['name'],
                    'product_slug' => $product['slug'],
                    'image' => $product['image'],
                    'min_stock' => $product['min_stock'],
                    'stock_status' => $this->stockStatus($variant['stock'], $product['min_stock']),
                ]);
            })
            ->values();
    }

    /**
     * @param  array{search?: string|null, category?: string|null, stock?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function inventory(array $filters = []): Collection
    {
        $rows = $this->variants()->map(fn (array $variant): array => [
            'product' => $variant['product'],
            'product_slug' => $variant['product_slug'],
            'variant' => $variant['color'].' / '.$variant['size'],
            'sku' => $variant['sku'],
            'stock' => $variant['stock'],
            'min_stock' => $variant['min_stock'],
            'status' => $variant['stock_status'],
            'category' => $this->product($variant['product_slug'])['category'] ?? '',
        ]);

        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $rows = $rows->filter(fn (array $row): bool => Str::contains(Str::lower($row['product'].' '.$row['sku'].' '.$row['variant']), $search));
        }

        if (filled($filters['category'] ?? null)) {
            $rows = $rows->where('category', $filters['category']);
        }

        if (filled($filters['stock'] ?? null)) {
            $rows = $rows->where('status', $filters['stock']);
        }

        return $rows->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function movements(): array
    {
        return [
            ['date' => '2026-09-02 09:14', 'product' => 'Basic Shirt', 'type' => 'sale', 'qty' => -1, 'before' => 43, 'after' => 42, 'user' => 'Ayşe Yılmaz', 'reference' => 'NOVA-1024'],
            ['date' => '2026-09-02 09:14', 'product' => 'Tailored Trouser', 'type' => 'sale', 'qty' => -1, 'before' => 23, 'after' => 22, 'user' => 'Ayşe Yılmaz', 'reference' => 'NOVA-1024'],
            ['date' => '2026-09-02 09:14', 'product' => 'Basic Shirt', 'type' => 'sale', 'qty' => -1, 'before' => 42, 'after' => 41, 'user' => 'Ayşe Yılmaz', 'reference' => 'NV-10482'],
            ['date' => '2026-09-02 08:51', 'product' => 'Wool Coat', 'type' => 'sale', 'qty' => -1, 'before' => 9, 'after' => 8, 'user' => 'Ayşe Yılmaz', 'reference' => 'NV-10481'],
            ['date' => '2026-09-01 18:20', 'product' => 'Cotton T-Shirt', 'type' => 'return', 'qty' => 1, 'before' => 37, 'after' => 38, 'user' => 'Mert Kaya', 'reference' => 'RT-2204'],
            ['date' => '2026-09-01 14:05', 'product' => 'Merino Crew Knit', 'type' => 'exchange', 'qty' => -1, 'before' => 5, 'after' => 4, 'user' => 'Ayşe Yılmaz', 'reference' => 'EX-118'],
            ['date' => '2026-08-30 11:40', 'product' => 'Tailored Trouser', 'type' => 'purchase', 'qty' => 20, 'before' => 2, 'after' => 22, 'user' => 'Deniz Aksoy', 'reference' => 'PO-441'],
            ['date' => '2026-08-29 16:12', 'product' => 'Fluid Silk Midi Dress', 'type' => 'manual', 'qty' => -2, 'before' => 2, 'after' => 0, 'user' => 'Deniz Aksoy', 'reference' => 'ADJ-19'],
            ['date' => '2026-08-28 10:02', 'product' => 'Leather Belt', 'type' => 'sale', 'qty' => -2, 'before' => 33, 'after' => 31, 'user' => 'Mert Kaya', 'reference' => 'NV-10390'],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function customers(): Collection
    {
        return collect([
            ['id' => 'elif-kaya', 'name' => 'Elif Kaya', 'phone' => '0532 441 12 08', 'email' => 'elif.kaya@email.com', 'orders' => 14, 'spent' => 24800, 'last_purchase' => '2026-09-02', 'city' => 'Istanbul'],
            ['id' => 'mert-aydin', 'name' => 'Mert Aydın', 'phone' => '0533 210 88 41', 'email' => 'mert.aydin@email.com', 'orders' => 6, 'spent' => 9720, 'last_purchase' => '2026-09-01', 'city' => 'Ankara'],
            ['id' => 'selin-arslan', 'name' => 'Selin Arslan', 'phone' => '0542 118 03 76', 'email' => 'selin.arslan@email.com', 'orders' => 21, 'spent' => 41250, 'last_purchase' => '2026-08-30', 'city' => 'Izmir'],
            ['id' => 'can-demir', 'name' => 'Can Demir', 'phone' => '0505 667 91 20', 'email' => 'can.demir@email.com', 'orders' => 3, 'spent' => 3180, 'last_purchase' => '2026-08-22', 'city' => 'Bursa'],
            ['id' => 'deniz-yildiz', 'name' => 'Deniz Yıldız', 'phone' => '0536 904 55 12', 'email' => 'deniz.yildiz@email.com', 'orders' => 9, 'spent' => 15640, 'last_purchase' => '2026-08-18', 'city' => 'Istanbul'],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function customer(string $id): ?array
    {
        $customer = $this->customers()->firstWhere('id', $id);

        if ($customer === null) {
            return null;
        }

        $customer['sales'] = [
            ['ref' => 'NV-10482', 'date' => '2026-09-02', 'total' => 1860, 'items' => 'Basic Shirt, Leather Belt'],
            ['ref' => 'NV-10311', 'date' => '2026-08-14', 'total' => 2499, 'items' => 'Wool Coat'],
            ['ref' => 'NV-10104', 'date' => '2026-07-02', 'total' => 899, 'items' => 'Basic Shirt'],
        ];
        $customer['returns'] = [
            ['ref' => 'RT-2204', 'date' => '2026-09-01', 'total' => 449, 'items' => 'Cotton T-Shirt / White / M'],
        ];

        return $customer;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function posItems(): Collection
    {
        return $this->variants()->map(fn (array $variant): array => [
            'sku' => $variant['sku'],
            'barcode' => $variant['barcode'],
            'name' => $variant['product'],
            'variant' => $variant['color'].' / '.$variant['size'],
            'price' => $variant['price'],
            'stock' => $variant['stock'],
            'image' => $variant['image'],
        ]);
    }

    /**
     * @param  array{search?: string|null, status?: string|null, date?: string|null, sort?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function suppliers(array $filters = []): Collection
    {
        $rows = collect($this->supplierCatalog());
        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $rows = $rows->filter(function (array $supplier) use ($search): bool {
                return Str::contains(Str::lower($supplier['name'].' '.$supplier['contact'].' '.$supplier['email']), $search);
            });
        }

        if (filled($filters['status'] ?? null)) {
            $rows = $rows->where('status', $filters['status']);
        }

        if (filled($filters['date'] ?? null)) {
            $rows = $rows->where('last_purchase', $filters['date']);
        }

        $sort = $filters['sort'] ?? 'name';

        $rows = match ($sort) {
            'purchases' => $rows->sortByDesc('total'),
            'recent' => $rows->sortByDesc('last_purchase'),
            default => $rows->sortBy('name'),
        };

        return $rows->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function supplier(string $id): ?array
    {
        $supplier = collect($this->supplierCatalog())->firstWhere('id', $id);

        if ($supplier === null) {
            return null;
        }

        $supplier['history'] = collect($this->purchases())
            ->where('supplier_id', $id)
            ->values()
            ->all();
        $supplier['movements'] = collect($this->movements())
            ->filter(fn (array $row): bool => collect($supplier['history'])->contains('number', $row['reference']))
            ->values()
            ->all();

        return $supplier;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function purchases(): array
    {
        return [
            ['number' => 'PO-441', 'supplier_id' => 'atelier-mills', 'date' => '2026-08-30', 'products' => 'Tailored Trouser ×20', 'total' => 14200, 'status' => 'received'],
            ['number' => 'PO-438', 'supplier_id' => 'atelier-mills', 'date' => '2026-08-12', 'products' => 'Wool Coat ×8', 'total' => 9440, 'status' => 'received'],
            ['number' => 'PO-402', 'supplier_id' => 'studio-textiles', 'date' => '2026-07-18', 'products' => 'Merino Crew Knit ×12', 'total' => 6480, 'status' => 'received'],
            ['number' => 'PO-390', 'supplier_id' => 'nova-leather', 'date' => '2026-06-04', 'products' => 'Leather Belt ×40', 'total' => 9600, 'status' => 'open'],
        ];
    }

    /**
     * @param  array{search?: string|null, from?: string|null, to?: string|null, payment?: string|null, cashier?: string|null, status?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function sales(array $filters = []): Collection
    {
        $rows = collect($this->saleCatalog());
        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $rows = $rows->filter(function (array $sale) use ($search): bool {
                return Str::contains(Str::lower($sale['number'].' '.$sale['customer']), $search);
            });
        }

        if (filled($filters['payment'] ?? null)) {
            $rows = $rows->where('payment', $filters['payment']);
        }

        if (filled($filters['cashier'] ?? null)) {
            $rows = $rows->where('cashier', $filters['cashier']);
        }

        if (filled($filters['status'] ?? null)) {
            $rows = $rows->where('status', $filters['status']);
        }

        if (filled($filters['from'] ?? null)) {
            $from = $filters['from'];
            $rows = $rows->filter(fn (array $sale): bool => $sale['date'] >= $from);
        }

        if (filled($filters['to'] ?? null)) {
            $to = $filters['to'];
            $rows = $rows->filter(fn (array $sale): bool => $sale['date'] <= $to.' 23:59');
        }

        return $rows->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function sale(string $id): ?array
    {
        $sale = collect($this->saleCatalog())->first(fn (array $row): bool => $row['id'] === $id || $row['number'] === $id);

        if ($sale === null) {
            return null;
        }

        $sale['stock_effects'] = collect($this->movements())
            ->where('reference', $sale['number'])
            ->values()
            ->all();
        $sale['cash_effects'] = collect($this->cashMovements())
            ->where('reference', $sale['number'])
            ->values()
            ->all();

        return $sale;
    }

    /**
     * @return list<string>
     */
    public function cashiers(): array
    {
        return ['Ayşe Yılmaz', 'Mert Kaya'];
    }

    /**
     * @param  array{return?: string|null, sale?: string|null, customer?: string|null, date?: string|null, reason?: string|null, status?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function returns(array $filters = []): Collection
    {
        $rows = collect($this->returnCatalog());
        $searchReturn = Str::lower(trim((string) ($filters['return'] ?? '')));
        $searchSale = Str::lower(trim((string) ($filters['sale'] ?? '')));
        $searchCustomer = Str::lower(trim((string) ($filters['customer'] ?? '')));

        if ($searchReturn !== '') {
            $rows = $rows->filter(fn (array $row): bool => Str::contains(Str::lower($row['number']), $searchReturn));
        }

        if ($searchSale !== '') {
            $rows = $rows->filter(fn (array $row): bool => Str::contains(Str::lower($row['sale']), $searchSale));
        }

        if ($searchCustomer !== '') {
            $rows = $rows->filter(fn (array $row): bool => Str::contains(Str::lower($row['customer']), $searchCustomer));
        }

        foreach (['date' => 'date', 'reason' => 'reason', 'status' => 'status'] as $filter => $field) {
            if (filled($filters[$filter] ?? null)) {
                $rows = $rows->where($field, $filters[$filter]);
            }
        }

        return $rows->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function returnRecord(string $id): ?array
    {
        $record = collect($this->returnCatalog())->firstWhere('id', $id);

        if ($record === null) {
            return null;
        }

        $record['stock_effects'] = collect($this->movements())
            ->where('reference', $record['number'])
            ->values()
            ->all();
        $record['cash_effects'] = collect($this->cashMovements())
            ->where('reference', $record['number'])
            ->values()
            ->all();

        return $record;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function exchanges(): Collection
    {
        return collect($this->exchangeCatalog());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function exchange(string $id): ?array
    {
        return collect($this->exchangeCatalog())->firstWhere('id', $id);
    }

    /**
     * @return array<string, mixed>
     */
    public function cashRegister(): array
    {
        return [
            'open' => true,
            'date' => '2026-09-02',
            'user' => 'Ayşe Yılmaz',
            'opening' => 12000,
            'current' => 55290,
            'today_sales' => 48250,
            'today_expenses' => 620,
            'today_refunds' => 4340,
            'expected' => 55290,
            'actual' => null,
            'difference' => null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function cashMovements(): array
    {
        return [
            ['date' => '2026-09-02 08:00', 'type' => 'opening', 'description' => 'Register opened', 'reference' => 'CR-0902', 'amount' => 12000, 'balance' => 12000, 'user' => 'Ayşe Yılmaz', 'flow' => 'in'],
            ['date' => '2026-09-02 09:14', 'type' => 'sale', 'description' => 'NOVA-1024 cash sale', 'reference' => 'NOVA-1024', 'amount' => 2398, 'balance' => 14398, 'user' => 'Ayşe Yılmaz', 'flow' => 'in'],
            ['date' => '2026-09-02 09:14', 'type' => 'sale', 'description' => 'NV-10482 cash sale', 'reference' => 'NV-10482', 'amount' => 1860, 'balance' => 15720, 'user' => 'Ayşe Yılmaz', 'flow' => 'in'],
            ['date' => '2026-09-01 18:20', 'type' => 'refund', 'description' => 'RT-2204 refund', 'reference' => 'RT-2204', 'amount' => -449, 'balance' => 13860, 'user' => 'Mert Kaya', 'flow' => 'out'],
            ['date' => '2026-09-02 11:40', 'type' => 'expense', 'description' => 'Packaging supplies', 'reference' => 'EX-19', 'amount' => -620, 'balance' => 54670, 'user' => 'Ayşe Yılmaz', 'flow' => 'out'],
            ['date' => '2026-09-01 10:02', 'type' => 'income', 'description' => 'Alteration fee', 'reference' => 'IN-08', 'amount' => 250, 'balance' => 14310, 'user' => 'Mert Kaya', 'flow' => 'in'],
            ['date' => '2026-09-01 21:05', 'type' => 'closing', 'description' => 'Register closed', 'reference' => 'CR-0901', 'amount' => 0, 'balance' => 14060, 'user' => 'Mert Kaya', 'flow' => 'neutral'],
            ['date' => '2026-08-30 16:12', 'type' => 'adjustment', 'description' => 'Till count correction', 'reference' => 'ADJ-04', 'amount' => -40, 'balance' => 11960, 'user' => 'Deniz Aksoy', 'flow' => 'out'],
        ];
    }

    /**
     * @param  array{type?: string|null, category?: string|null, date?: string|null, user?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function incomeExpenses(array $filters = []): Collection
    {
        $rows = collect($this->incomeExpenseCatalog());

        foreach (['type', 'category', 'date', 'user'] as $key) {
            if (filled($filters[$key] ?? null)) {
                $rows = $rows->where($key, $filters[$key]);
            }
        }

        return $rows->values();
    }

    /**
     * @return list<string>
     */
    public function incomeExpenseCategories(): array
    {
        return ['Packaging', 'Alterations', 'Shipping', 'Utilities', 'Other'];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function payments(): Collection
    {
        return collect([
            ['date' => '2026-09-02 09:14', 'sale' => 'NOVA-1024', 'customer' => 'Elif Kaya', 'method' => 'cash', 'amount' => 2398, 'status' => 'completed', 'reference' => 'PAY-1024'],
            ['date' => '2026-09-02 08:51', 'sale' => 'NV-10481', 'customer' => 'Mert Aydın', 'method' => 'card', 'amount' => 2499, 'status' => 'completed', 'reference' => 'PAY-10481'],
            ['date' => '2026-09-01 16:40', 'sale' => 'NV-10390', 'customer' => 'Selin Arslan', 'method' => 'other', 'amount' => 1380, 'status' => 'completed', 'reference' => 'PAY-10390'],
            ['date' => '2026-09-01 18:20', 'sale' => 'NV-10311', 'customer' => 'Elif Kaya', 'method' => 'cash', 'amount' => -449, 'status' => 'refunded', 'reference' => 'PAY-RT-2204'],
        ]);
    }

    /**
     * @return list<array{key: string, label: string, route: string}>
     */
    public function reportCategories(): array
    {
        return [
            ['key' => 'sales', 'label' => __('admin.reports.categories.sales'), 'route' => 'sales'],
            ['key' => 'products', 'label' => __('admin.reports.categories.products'), 'route' => 'products'],
            ['key' => 'inventory', 'label' => __('admin.reports.categories.inventory'), 'route' => 'inventory'],
            ['key' => 'cash', 'label' => __('admin.reports.categories.cash'), 'route' => 'cash'],
            ['key' => 'returns', 'label' => __('admin.reports.categories.returns'), 'route' => 'returns'],
            ['key' => 'customers', 'label' => __('admin.reports.categories.customers'), 'route' => 'customers'],
            ['key' => 'suppliers', 'label' => __('admin.reports.categories.suppliers'), 'route' => 'suppliers'],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function report(string $category): ?array
    {
        $meta = collect($this->reportCategories())->firstWhere('key', $category);

        if ($meta === null) {
            return null;
        }

        $rows = match ($category) {
            'sales' => [
                ['label' => 'Today', 'value' => self::money(48250)],
                ['label' => 'Orders', 'value' => '126'],
                ['label' => 'Average ticket', 'value' => self::money(383)],
            ],
            'products' => [
                ['label' => 'Basic Shirt', 'value' => '38 units'],
                ['label' => 'Cotton T-Shirt', 'value' => '29 units'],
                ['label' => 'Wool Coat', 'value' => '9 units'],
            ],
            'inventory' => [
                ['label' => 'Low stock SKUs', 'value' => '18'],
                ['label' => 'Out of stock', 'value' => '2'],
                ['label' => 'Units on hand', 'value' => '160'],
            ],
            'cash' => [
                ['label' => 'Opening', 'value' => self::money(12000)],
                ['label' => "Today's sales", 'value' => self::money(48250)],
                ['label' => 'Expected', 'value' => self::money(55290)],
            ],
            'returns' => [
                ['label' => 'Returns today', 'value' => '7'],
                ['label' => 'Return rate', 'value' => '5.6%'],
                ['label' => 'Refunded', 'value' => self::money(4340)],
            ],
            'customers' => [
                ['label' => 'Active buyers', 'value' => '5'],
                ['label' => 'Top spender', 'value' => 'Selin Arslan'],
                ['label' => 'Repeat rate', 'value' => '68%'],
            ],
            'suppliers' => [
                ['label' => 'Active suppliers', 'value' => '2'],
                ['label' => 'Open POs', 'value' => '1'],
                ['label' => 'Outstanding', 'value' => self::money(8400)],
            ],
            default => [],
        };

        return [
            'key' => $category,
            'title' => $meta['label'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboard(string $range, AdminStaff $staff): array
    {
        $metrics = match ($range) {
            '7d' => ['sales' => 318400, 'orders' => 842, 'low_stock' => 18, 'returns' => 41],
            '30d' => ['sales' => 1240000, 'orders' => 3210, 'low_stock' => 18, 'returns' => 156],
            default => ['sales' => 48250, 'orders' => 126, 'low_stock' => 18, 'returns' => 7],
        };

        $series = match ($range) {
            '7d' => [
                ['label' => 'Thu', 'value' => 41200],
                ['label' => 'Fri', 'value' => 53800],
                ['label' => 'Sat', 'value' => 67400],
                ['label' => 'Sun', 'value' => 48100],
                ['label' => 'Mon', 'value' => 35600],
                ['label' => 'Tue', 'value' => 24050],
                ['label' => 'Wed', 'value' => 48250],
            ],
            '30d' => [
                ['label' => 'W1', 'value' => 268000],
                ['label' => 'W2', 'value' => 301000],
                ['label' => 'W3', 'value' => 289000],
                ['label' => 'W4', 'value' => 382000],
            ],
            default => [
                ['label' => '09', 'value' => 4200],
                ['label' => '10', 'value' => 6800],
                ['label' => '11', 'value' => 9100],
                ['label' => '12', 'value' => 5400],
                ['label' => '13', 'value' => 4700],
                ['label' => '14', 'value' => 7600],
                ['label' => '15', 'value' => 6200],
                ['label' => '16', 'value' => 4250],
            ],
        };

        $hour = now()->hour;
        $greetingKey = $hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening');

        return [
            'range' => $range,
            'greeting' => __('admin.dashboard.greeting.'.$greetingKey, [
                'name' => Str::of($staff->name)->before(' ')->toString(),
            ]),
            'today_label' => now()->translatedFormat('l, F j'),
            'kpis' => [
                ['key' => 'sales', 'label' => __('admin.dashboard.kpis.sales'), 'value' => self::money($metrics['sales'])],
                ['key' => 'orders', 'label' => __('admin.dashboard.kpis.orders'), 'value' => number_format($metrics['orders'])],
                ['key' => 'low_stock', 'label' => __('admin.dashboard.kpis.low_stock'), 'value' => (string) $metrics['low_stock']],
                ['key' => 'returns', 'label' => __('admin.dashboard.kpis.returns'), 'value' => (string) $metrics['returns']],
            ],
            'salesSeries' => $series,
            'topProducts' => [
                ['name' => 'Basic Shirt', 'qty' => 38, 'amount' => 34162, 'share' => 72],
                ['name' => 'Cotton T-Shirt', 'qty' => 29, 'amount' => 13021, 'share' => 54],
                ['name' => 'Wool Coat', 'qty' => 9, 'amount' => 22491, 'share' => 48],
                ['name' => 'Tailored Trouser', 'qty' => 11, 'amount' => 17490, 'share' => 36],
            ],
            'inventoryAlerts' => $this->inventory(['stock' => 'low_stock'])->take(5)->values(),
            'returnRate' => $range === '30d' ? 4.9 : ($range === '7d' ? 4.8 : 5.6),
            'cash' => [
                ['label' => __('admin.dashboard.cash.opening'), 'value' => self::money(12000)],
                ['label' => __('admin.dashboard.cash.sales'), 'value' => self::money($metrics['sales'])],
                ['label' => __('admin.dashboard.cash.refunds'), 'value' => self::money($metrics['returns'] * 620)],
                ['label' => __('admin.dashboard.cash.expected'), 'value' => self::money(12000 + $metrics['sales'] - ($metrics['returns'] * 620))],
            ],
        ];
    }

    public function stockStatus(int $stock, int $minStock): string
    {
        if ($stock <= 0) {
            return 'out_of_stock';
        }

        if ($stock <= $minStock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function supplierCatalog(): array
    {
        return [
            [
                'id' => 'atelier-mills',
                'name' => 'Atelier Mills',
                'contact' => 'Hakan Demir',
                'phone' => '0212 555 10 20',
                'email' => 'hakan@ateliermills.com',
                'address' => 'Merkez Mah. Atatürk Cad. No:12, Istanbul',
                'tax' => '1234567890',
                'purchases' => 12,
                'total' => 186400,
                'last_purchase' => '2026-08-30',
                'status' => 'active',
                'balance' => 8400,
            ],
            [
                'id' => 'studio-textiles',
                'name' => 'Studio Textiles',
                'contact' => 'Lara Koç',
                'phone' => '0232 441 08 11',
                'email' => 'lara@studiotextiles.com',
                'address' => 'Alsancak Liman Cad. 8, Izmir',
                'tax' => '9876543210',
                'purchases' => 4,
                'total' => 28600,
                'last_purchase' => '2026-07-18',
                'status' => 'active',
                'balance' => 0,
            ],
            [
                'id' => 'nova-leather',
                'name' => 'NOVA Leather Co.',
                'contact' => 'Emre Şahin',
                'phone' => '0312 220 44 90',
                'email' => 'emre@novaleather.com',
                'address' => 'Ostim OSB 12. Cad. 5, Ankara',
                'tax' => '1122334455',
                'purchases' => 2,
                'total' => 9600,
                'last_purchase' => '2026-06-04',
                'status' => 'inactive',
                'balance' => 9600,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function saleCatalog(): array
    {
        return [
            [
                'id' => 'nova-1024',
                'number' => 'NOVA-1024',
                'date' => '2026-09-02 09:14',
                'customer' => 'Elif Kaya',
                'customer_id' => 'elif-kaya',
                'items_count' => 2,
                'items_label' => 'Basic Shirt, Tailored Trouser',
                'items' => [
                    ['product' => 'Basic Shirt', 'variant' => 'White / M', 'sku' => 'NOVA01-WHI-M', 'qty' => 1, 'unit' => 899, 'discount' => 0, 'total' => 899],
                    ['product' => 'Tailored Trouser', 'variant' => 'Black / 46', 'sku' => 'NOVA05-BLK-46', 'qty' => 1, 'unit' => 1499, 'discount' => 0, 'total' => 1499],
                ],
                'subtotal' => 2398,
                'discount' => 0,
                'total' => 2398,
                'payment' => 'cash',
                'cashier' => 'Ayşe Yılmaz',
                'status' => 'completed',
            ],
            [
                'id' => 'nv-10482',
                'number' => 'NV-10482',
                'date' => '2026-09-02 09:14',
                'customer' => 'Elif Kaya',
                'customer_id' => 'elif-kaya',
                'items_count' => 2,
                'items_label' => 'Basic Shirt, Leather Belt',
                'items' => [
                    ['product' => 'Basic Shirt', 'variant' => 'White / M', 'sku' => 'NOVA01-WHI-M', 'qty' => 1, 'unit' => 899, 'discount' => 0, 'total' => 899],
                    ['product' => 'Leather Belt', 'variant' => 'Cognac / 85', 'sku' => 'NOVA07-CGN-85', 'qty' => 1, 'unit' => 961, 'discount' => 0, 'total' => 961],
                ],
                'subtotal' => 1860,
                'discount' => 0,
                'total' => 1860,
                'payment' => 'cash',
                'cashier' => 'Ayşe Yılmaz',
                'status' => 'completed',
            ],
            [
                'id' => 'nv-10481',
                'number' => 'NV-10481',
                'date' => '2026-09-02 08:51',
                'customer' => 'Mert Aydın',
                'customer_id' => 'mert-aydin',
                'items_count' => 1,
                'items_label' => 'Wool Coat',
                'items' => [
                    ['product' => 'Wool Coat', 'variant' => 'Camel / M', 'sku' => 'NOVA02-CML-M', 'qty' => 1, 'unit' => 2499, 'discount' => 0, 'total' => 2499],
                ],
                'subtotal' => 2499,
                'discount' => 0,
                'total' => 2499,
                'payment' => 'card',
                'cashier' => 'Ayşe Yılmaz',
                'status' => 'completed',
            ],
            [
                'id' => 'nv-10390',
                'number' => 'NV-10390',
                'date' => '2026-08-28 10:02',
                'customer' => 'Selin Arslan',
                'customer_id' => 'selin-arslan',
                'items_count' => 2,
                'items_label' => 'Leather Belt',
                'items' => [
                    ['product' => 'Leather Belt', 'variant' => 'Black / 90', 'sku' => 'NOVA07-BLK-90', 'qty' => 2, 'unit' => 690, 'discount' => 0, 'total' => 1380],
                ],
                'subtotal' => 1380,
                'discount' => 0,
                'total' => 1380,
                'payment' => 'other',
                'cashier' => 'Mert Kaya',
                'status' => 'cancelled',
            ],
            [
                'id' => 'nv-10311',
                'number' => 'NV-10311',
                'date' => '2026-08-14 16:20',
                'customer' => 'Elif Kaya',
                'customer_id' => 'elif-kaya',
                'items_count' => 1,
                'items_label' => 'Cotton T-Shirt',
                'items' => [
                    ['product' => 'Cotton T-Shirt', 'variant' => 'White / M', 'sku' => 'NOVA03-WHI-M', 'qty' => 1, 'unit' => 449, 'discount' => 0, 'total' => 449],
                ],
                'subtotal' => 449,
                'discount' => 0,
                'total' => 449,
                'payment' => 'cash',
                'cashier' => 'Mert Kaya',
                'status' => 'returned',
            ],
            [
                'id' => 'nv-10104',
                'number' => 'NV-10104',
                'date' => '2026-07-02 13:08',
                'customer' => 'Elif Kaya',
                'customer_id' => 'elif-kaya',
                'items_count' => 2,
                'items_label' => 'Basic Shirt, Cotton T-Shirt',
                'items' => [
                    ['product' => 'Basic Shirt', 'variant' => 'Blue / L', 'sku' => 'NOVA01-BLU-L', 'qty' => 1, 'unit' => 899, 'discount' => 0, 'total' => 899],
                    ['product' => 'Cotton T-Shirt', 'variant' => 'Black / M', 'sku' => 'NOVA03-BLA-M', 'qty' => 1, 'unit' => 449, 'discount' => 0, 'total' => 449],
                ],
                'subtotal' => 1348,
                'discount' => 0,
                'total' => 1348,
                'payment' => 'card',
                'cashier' => 'Ayşe Yılmaz',
                'status' => 'partially_returned',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function returnCatalog(): array
    {
        return [
            [
                'id' => 'rt-2204',
                'number' => 'RT-2204',
                'sale' => 'NV-10311',
                'customer' => 'Elif Kaya',
                'products' => 'Cotton T-Shirt / White / M',
                'amount' => 449,
                'reason' => 'wrong_size',
                'date' => '2026-09-01',
                'status' => 'completed',
                'type' => 'full',
                'refund' => 449,
            ],
            [
                'id' => 'rt-2210',
                'number' => 'RT-2210',
                'sale' => 'NV-10104',
                'customer' => 'Elif Kaya',
                'products' => 'Cotton T-Shirt / Black / M',
                'amount' => 449,
                'reason' => 'customer_request',
                'date' => '2026-08-20',
                'status' => 'completed',
                'type' => 'partial',
                'refund' => 449,
            ],
            [
                'id' => 'rt-2188',
                'number' => 'RT-2188',
                'sale' => 'NV-10390',
                'customer' => 'Selin Arslan',
                'products' => 'Leather Belt / Black / 90',
                'amount' => 690,
                'reason' => 'defective',
                'date' => '2026-08-29',
                'status' => 'open',
                'type' => 'partial',
                'refund' => 0,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function exchangeCatalog(): array
    {
        return [
            [
                'id' => 'ex-118',
                'number' => 'EX-118',
                'date' => '2026-09-01',
                'customer' => 'Elif Kaya',
                'original' => ['product' => 'Cotton T-Shirt', 'variant' => 'Black / M', 'sku' => 'NOVA03-BLA-M', 'price' => 449],
                'new' => ['product' => 'Cotton T-Shirt', 'variant' => 'Black / L', 'sku' => 'NOVA03-BLA-L', 'price' => 449],
                'difference' => 'no_difference',
                'difference_amount' => 0,
                'status' => 'completed',
            ],
            [
                'id' => 'ex-121',
                'number' => 'EX-121',
                'date' => '2026-08-22',
                'customer' => 'Can Demir',
                'original' => ['product' => 'Basic Shirt', 'variant' => 'White / M', 'sku' => 'NOVA01-WHI-M', 'price' => 899],
                'new' => ['product' => 'Wool Coat', 'variant' => 'Camel / S', 'sku' => 'NOVA02-CML-S', 'price' => 2499],
                'difference' => 'additional_payment',
                'difference_amount' => 1600,
                'status' => 'completed',
            ],
            [
                'id' => 'ex-109',
                'number' => 'EX-109',
                'date' => '2026-08-10',
                'customer' => 'Deniz Yıldız',
                'original' => ['product' => 'Wool Coat', 'variant' => 'Black / M', 'sku' => 'NOVA02-BLK-M', 'price' => 2499],
                'new' => ['product' => 'Structured Blazer', 'variant' => 'Navy / 46', 'sku' => 'NOVA08-NVY-46', 'price' => 2190],
                'difference' => 'refund',
                'difference_amount' => -309,
                'status' => 'completed',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function incomeExpenseCatalog(): array
    {
        return [
            ['id' => 'ex-19', 'date' => '2026-09-02', 'type' => 'expense', 'category' => 'Packaging', 'description' => 'Packaging supplies', 'amount' => 620, 'user' => 'Ayşe Yılmaz', 'reference' => 'EX-19'],
            ['id' => 'in-08', 'date' => '2026-09-01', 'type' => 'income', 'category' => 'Alterations', 'description' => 'Alteration fee', 'amount' => 250, 'user' => 'Mert Kaya', 'reference' => 'IN-08'],
            ['id' => 'ex-14', 'date' => '2026-08-28', 'type' => 'expense', 'category' => 'Shipping', 'description' => 'Supplier inbound freight', 'amount' => 480, 'user' => 'Deniz Aksoy', 'reference' => 'EX-14'],
            ['id' => 'in-04', 'date' => '2026-08-20', 'type' => 'income', 'category' => 'Other', 'description' => 'Gift wrap', 'amount' => 90, 'user' => 'Ayşe Yılmaz', 'reference' => 'IN-04'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function catalog(): array
    {
        $shirt = $this->makeProduct(
            id: 1,
            name: 'Basic Shirt',
            sku: 'NOVA01',
            barcode: '8680001000012',
            category: 'Shirts',
            brand: 'NOVA',
            price: 899,
            purchase: 420,
            stock: 42,
            min: 10,
            image: 'photo-1596755094514-f87e34085b83',
            description: 'A clean cotton shirt with a precise collar and straight hem.',
            variants: $this->sizeColorMatrix('NOVA01', 899, '8680001000', ['White', 'Blue'], ['S', 'M', 'L'], 7),
        );

        $coat = $this->makeProduct(
            id: 2,
            name: 'Wool Coat',
            sku: 'NOVA02',
            barcode: '8680001000029',
            category: 'Outerwear',
            brand: 'Atelier',
            price: 2499,
            purchase: 1180,
            stock: 8,
            min: 10,
            image: 'photo-1539109136881-3be0616acf4b',
            description: 'Double-faced wool coat with a straight architectural line.',
            variants: [
                $this->variant('NOVA02-CML-S', '8680001000104', 'S', 'Camel', 3, 2499),
                $this->variant('NOVA02-CML-M', '8680001000111', 'M', 'Camel', 3, 2499),
                $this->variant('NOVA02-BLK-M', '8680001000128', 'M', 'Black', 2, 2499),
            ],
        );

        $tee = $this->makeProduct(
            id: 3,
            name: 'Cotton T-Shirt',
            sku: 'NOVA03',
            barcode: '8680001000036',
            category: 'Shirts',
            brand: 'NOVA',
            price: 449,
            purchase: 160,
            stock: 38,
            min: 12,
            image: 'photo-1521572163474-6864f9cf17ab',
            description: 'Compact cotton jersey T-shirt. Core retail SKU.',
            variants: $this->sizeColorMatrix('NOVA03', 449, '8680001001', ['Black', 'White'], ['XS', 'S', 'M', 'L', 'XL'], 4),
        );

        $knit = $this->makeProduct(
            id: 4,
            name: 'Merino Crew Knit',
            sku: 'NOVA04',
            barcode: '8680001000043',
            category: 'Knitwear',
            brand: 'Studio',
            price: 1290,
            purchase: 540,
            stock: 4,
            min: 8,
            image: 'photo-1434389677669-e08b4cac3105',
            description: 'Fine merino crew with a dry handfeel.',
            variants: [
                $this->variant('NOVA04-OAT-M', '8680001000203', 'M', 'Oat', 2, 1290),
                $this->variant('NOVA04-CHR-L', '8680001000210', 'L', 'Charcoal', 2, 1290),
            ],
        );

        $trouser = $this->makeProduct(
            id: 5,
            name: 'Tailored Trouser',
            sku: 'NOVA05',
            barcode: '8680001000050',
            category: 'Trousers',
            brand: 'Atelier',
            price: 1590,
            purchase: 710,
            stock: 22,
            min: 8,
            image: 'photo-1594633312681-425c7b97b4a0',
            description: 'Pressed wool trouser with a clean crease.',
            variants: [
                $this->variant('NOVA05-BLK-46', '8680001000302', '46', 'Black', 10, 1590),
                $this->variant('NOVA05-BLK-48', '8680001000319', '48', 'Black', 12, 1590),
            ],
        );

        $dress = $this->makeProduct(
            id: 6,
            name: 'Fluid Silk Midi Dress',
            sku: 'NOVA06',
            barcode: '8680001000067',
            category: 'Dresses',
            brand: 'Studio',
            price: 1890,
            purchase: 820,
            stock: 0,
            min: 4,
            image: 'photo-1496747613396-36d77e0ac6b5',
            description: 'Bias-cut silk midi with a quiet drape.',
            variants: [
                $this->variant('NOVA06-IVY-S', '8680001000401', 'S', 'Ivory', 0, 1890),
                $this->variant('NOVA06-IVY-M', '8680001000418', 'M', 'Ivory', 0, 1890),
            ],
        );

        $belt = $this->makeProduct(
            id: 7,
            name: 'Leather Belt',
            sku: 'NOVA07',
            barcode: '8680001000074',
            category: 'Accessories',
            brand: 'NOVA',
            price: 690,
            purchase: 240,
            stock: 31,
            min: 6,
            image: 'photo-1624222247344-550fb60583d2',
            description: 'Vegetable-tanned leather belt, 3 cm.',
            variants: [
                $this->variant('NOVA07-CGN-85', '8680001000500', '85', 'Cognac', 16, 690),
                $this->variant('NOVA07-BLK-90', '8680001000517', '90', 'Black', 15, 690),
            ],
        );

        $blazer = $this->makeProduct(
            id: 8,
            name: 'Structured Blazer',
            sku: 'NOVA08',
            barcode: '8680001000081',
            category: 'Outerwear',
            brand: 'Atelier',
            price: 2190,
            purchase: 980,
            stock: 15,
            min: 6,
            image: 'photo-1594938298603-c8148cfe4357',
            description: 'Single-breasted blazer with a narrow lapel.',
            variants: [
                $this->variant('NOVA08-NVY-46', '8680001000609', '46', 'Navy', 8, 2190),
                $this->variant('NOVA08-BLK-48', '8680001000616', '48', 'Black', 7, 2190),
            ],
        );

        return [$shirt, $coat, $tee, $knit, $trouser, $dress, $belt, $blazer];
    }

    /**
     * @param  list<array<string, mixed>>  $variants
     * @return array<string, mixed>
     */
    private function makeProduct(
        int $id,
        string $name,
        string $sku,
        string $barcode,
        string $category,
        string $brand,
        int $price,
        int $purchase,
        int $stock,
        int $min,
        string $image,
        string $description,
        array $variants,
    ): array {
        $total = collect($variants)->sum('stock');

        return [
            'id' => $id,
            'slug' => Str::slug($name),
            'name' => $name,
            'sku' => $sku,
            'barcode' => $barcode,
            'category' => $category,
            'brand' => $brand,
            'price' => $price,
            'purchase_price' => $purchase,
            'vat' => 20,
            'stock' => $total > 0 ? $total : $stock,
            'min_stock' => $min,
            'status' => ($total > 0 ? $total : $stock) <= 0 ? 'inactive' : 'active',
            'stock_status' => $this->stockStatus($total > 0 ? $total : $stock, $min),
            'image' => 'https://images.unsplash.com/'.$image.'?auto=format&fit=crop&w=160&q=80',
            'description' => $description,
            'variants' => $variants,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function variant(string $sku, string $barcode, string $size, string $color, int $stock, int $price): array
    {
        return [
            'sku' => $sku,
            'barcode' => $barcode,
            'size' => $size,
            'color' => $color,
            'stock' => $stock,
            'price' => $price,
        ];
    }

    /**
     * @param  list<string>  $colors
     * @param  list<string>  $sizes
     * @return list<array<string, mixed>>
     */
    private function sizeColorMatrix(string $prefix, int $price, string $barcodeBase, array $colors, array $sizes, int $each): array
    {
        $variants = [];
        $seq = 100;

        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $code = Str::upper(Str::substr($color, 0, 3)).'-'.$size;
                $variants[] = $this->variant(
                    $prefix.'-'.$code,
                    $barcodeBase.str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
                    $size,
                    $color,
                    $each,
                    $price,
                );
                $seq++;
            }
        }

        return $variants;
    }
}
