<?php

namespace App\Support;

use App\Enums\StaffRole;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class AdminStore
{
    /**
     * @var Collection<int, array<string, mixed>>|null
     */
    private ?Collection $productItems = null;

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
        return $this->productItems ??= $this->resolveProducts();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function resolveProducts(): Collection
    {
        $fromDatabase = $this->databaseProducts();

        if ($fromDatabase->isNotEmpty()) {
            return $fromDatabase;
        }

        return collect($this->catalog());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function databaseProducts(): Collection
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'catalog_code')) {
            return collect();
        }

        return Product::query()
            ->with(['category', 'images', 'variants.stock'])
            ->whereNull('catalog_code')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => $this->mapFromDatabase($product))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapFromDatabase(Product $product): array
    {
        $attributes = $product->attributes ?? [];
        $variants = $product->variants->map(function ($variant): array {
            return [
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'size' => $variant->size,
                'color' => $variant->color,
                'stock' => (int) ($variant->stock?->quantity ?? 0),
                'price' => (int) ($variant->price ?? $product->base_price),
            ];
        })->values()->all();

        $stock = (int) collect($variants)->sum('stock');
        $minStock = (int) ($product->variants->first()?->stock?->minimum_quantity ?? $attributes['min_stock'] ?? 0);
        $primaryImage = $product->images->first();

        return [
            'id' => $attributes['legacy_id'] ?? $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'sku' => $attributes['sku'] ?? $product->variants->first()?->sku ?? '',
            'barcode' => $attributes['barcode'] ?? $product->variants->first()?->barcode ?? '',
            'category' => $product->category?->name ?? '',
            'brand' => (string) $product->brand,
            'price' => (int) $product->base_price,
            'purchase_price' => (int) ($attributes['purchase_price'] ?? 0),
            'vat' => (int) ($attributes['vat'] ?? 20),
            'stock' => $stock,
            'min_stock' => $minStock,
            'status' => $product->is_active ? 'active' : 'inactive',
            'stock_status' => $this->stockStatus($stock, $minStock),
            'image' => $primaryImage?->image_url ?? '',
            'description' => (string) $product->description,
            'variants' => $variants,
        ];
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
     * @return Collection<int, array{id: string, name: string, products: int, stock: int, status: string}>
     */
    public function categoryRecords(): Collection
    {
        $products = $this->products();
        $names = collect($this->categories())
            ->merge($products->pluck('category')->filter())
            ->unique()
            ->values();

        return $names->map(function (string $name) use ($products): array {
            $rows = $products->where('category', $name);

            return [
                'id' => Str::slug($name),
                'name' => $name,
                'products' => $rows->count(),
                'stock' => (int) $rows->sum('stock'),
                'status' => $rows->contains('status', 'inactive') && $rows->doesntContain('status', 'active')
                    ? 'inactive'
                    : 'active',
            ];
        });
    }

    /**
     * @return Collection<int, array{id: string, name: string, products: int, stock: int, status: string}>
     */
    public function brandRecords(): Collection
    {
        $products = $this->products();
        $names = collect($this->brands())
            ->merge($products->pluck('brand')->filter())
            ->unique()
            ->values();

        return $names->map(function (string $name) use ($products): array {
            $rows = $products->where('brand', $name);

            return [
                'id' => Str::slug($name),
                'name' => $name,
                'products' => $rows->count(),
                'stock' => (int) $rows->sum('stock'),
                'status' => $rows->contains('status', 'inactive') && $rows->doesntContain('status', 'active')
                    ? 'inactive'
                    : 'active',
            ];
        });
    }

    /**
     * @param  array{search?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function barcodes(array $filters = []): Collection
    {
        $rows = $this->variants()->map(fn (array $variant): array => [
            'product' => $variant['product'],
            'product_slug' => $variant['product_slug'],
            'variant' => $variant['color'].' / '.$variant['size'],
            'sku' => $variant['sku'],
            'barcode' => $variant['barcode'],
            'stock' => $variant['stock'],
            'status' => $variant['stock_status'],
        ]);

        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $rows = $rows->filter(function (array $row) use ($search): bool {
                return Str::contains(Str::lower($row['product'].' '.$row['sku'].' '.$row['barcode']), $search);
            });
        }

        return $rows->values();
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
     * @param  array{search?: string|null, category?: string|null, brand?: string|null, stock?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function inventory(array $filters = []): Collection
    {
        $rows = $this->variants()->map(function (array $variant): array {
            $product = $this->product($variant['product_slug']) ?? [];
            $purchase = (int) ($product['purchase_price'] ?? 0);

            return [
                'product' => $variant['product'],
                'product_slug' => $variant['product_slug'],
                'variant' => $variant['color'].' / '.$variant['size'],
                'sku' => $variant['sku'],
                'stock' => $variant['stock'],
                'min_stock' => $variant['min_stock'],
                'status' => $variant['stock_status'],
                'category' => $product['category'] ?? '',
                'brand' => $product['brand'] ?? '',
                'purchase_price' => $purchase,
                'stock_value' => $variant['stock'] * $purchase,
            ];
        });

        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $rows = $rows->filter(fn (array $row): bool => Str::contains(Str::lower($row['product'].' '.$row['sku'].' '.$row['variant']), $search));
        }

        if (filled($filters['category'] ?? null)) {
            $rows = $rows->where('category', $filters['category']);
        }

        if (filled($filters['brand'] ?? null)) {
            $rows = $rows->where('brand', $filters['brand']);
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
        $rows = collect($this->customerCatalog())->keyBy('id');

        foreach ($this->databaseCustomers() as $row) {
            $rows->put($row['id'], [
                ...($rows->get($row['id']) ?? []),
                ...$row,
            ]);
        }

        foreach (session('admin.customers', []) as $id => $row) {
            $rows->put((string) $id, [
                ...($rows->get((string) $id) ?? []),
                ...$row,
            ]);
        }

        return $rows->values();
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null}  $data
     * @return array<string, mixed>
     */
    public function createCustomer(array $data): array
    {
        $base = Str::slug($data['name']);
        $id = $base === '' ? 'customer' : $base;
        $suffix = 2;

        while ($this->customers()->firstWhere('id', $id) !== null) {
            $id = $base.'-'.$suffix;
            $suffix++;
        }

        $record = [
            'id' => $id,
            'name' => $data['name'],
            'phone' => (string) ($data['phone'] ?? ''),
            'email' => $data['email'],
            'orders' => 0,
            'spent' => 0,
            'last_purchase' => '—',
            'city' => '',
            'created_at' => now()->toDateString(),
        ];

        $customers = session('admin.customers', []);
        $customers[$id] = $record;
        session(['admin.customers' => $customers]);

        (new DatabaseRecords)->saveCustomer($record);

        return $record;
    }

    /**
     * @param  array{name: string, contact?: string|null, email?: string|null, phone?: string|null, address?: string|null, tax?: string|null, status?: string|null}  $data
     * @return array<string, mixed>
     */
    public function createSupplier(array $data): array
    {
        $base = Str::slug($data['name']);
        $id = $base === '' ? 'supplier' : $base;
        $suffix = 2;

        while ($this->suppliers()->firstWhere('id', $id) !== null) {
            $id = $base.'-'.$suffix;
            $suffix++;
        }

        $record = [
            'id' => $id,
            'name' => $data['name'],
            'contact' => (string) ($data['contact'] ?? ''),
            'phone' => (string) ($data['phone'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
            'address' => (string) ($data['address'] ?? ''),
            'tax' => (string) ($data['tax'] ?? ''),
            'purchases' => 0,
            'total' => 0,
            'last_purchase' => '',
            'status' => ($data['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active',
            'balance' => 0,
        ];

        $suppliers = session('admin.suppliers', []);
        $suppliers[$id] = $record;
        session(['admin.suppliers' => $suppliers]);

        (new DatabaseRecords)->saveSupplier($record);

        return $record;
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

        if (collect($this->customerCatalog())->contains('id', $id)) {
            $customer['sales'] = [
                ['ref' => 'NV-10482', 'date' => '2026-09-02', 'total' => 1860, 'items' => 'Basic Shirt, Leather Belt'],
                ['ref' => 'NV-10311', 'date' => '2026-08-14', 'total' => 2499, 'items' => 'Wool Coat'],
                ['ref' => 'NV-10104', 'date' => '2026-07-02', 'total' => 899, 'items' => 'Basic Shirt'],
            ];
            $customer['returns'] = [
                ['ref' => 'RT-2204', 'date' => '2026-09-01', 'total' => 449, 'items' => 'Cotton T-Shirt / White / M'],
            ];
        } else {
            $customer['sales'] = [];
            $customer['returns'] = [];
        }

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
        $rows = collect($this->supplierCatalog())->keyBy('id');

        foreach ($this->databaseSuppliers() as $row) {
            $rows->put($row['id'], [
                ...($rows->get($row['id']) ?? []),
                ...$row,
            ]);
        }

        foreach (session('admin.suppliers', []) as $id => $row) {
            $rows->put((string) $id, [
                ...($rows->get((string) $id) ?? []),
                ...$row,
            ]);
        }

        $rows = $rows->values();
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
        $supplier = $this->suppliers()->firstWhere('id', $id);

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
     * @param  array{range?: string|null, from?: string|null, to?: string|null, date?: string|null, category?: string|null, brand?: string|null, stock?: string|null}  $filters
     * @return array<string, mixed>|null
     */
    public function report(string $category, array $filters = []): ?array
    {
        $meta = collect($this->reportCategories())->firstWhere('key', $category);

        if ($meta === null) {
            return null;
        }

        return match ($category) {
            'sales' => $this->salesReport($meta, $filters),
            'products' => $this->productsReport($meta),
            'inventory' => $this->inventoryReport($meta, $filters),
            'cash' => $this->cashReport($meta, $filters),
            'returns' => $this->returnReport($meta),
            'customers' => $this->customerReport($meta),
            'suppliers' => $this->supplierReport($meta),
            default => null,
        };
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function users(): Collection
    {
        $users = collect($this->userCatalog())->keyBy('id');

        foreach ($this->databaseUsers() as $row) {
            $users->put($row['id'], [
                ...($users->get($row['id']) ?? []),
                ...$row,
            ]);
        }

        foreach (session('admin.staff', []) as $id => $row) {
            $users->put((string) $id, [
                ...($users->get((string) $id) ?? []),
                ...$row,
            ]);
        }

        return $users->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function user(string $id): ?array
    {
        return $this->users()->firstWhere('id', $id);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function staffByUsername(string $username): ?array
    {
        $needle = Str::lower(trim($username));

        return $this->users()->first(
            fn (array $user): bool => Str::lower((string) ($user['username'] ?? '')) === $needle
        );
    }

    /**
     * @param  array<string, mixed>  $staff
     */
    public function passwordMatches(array $staff, string $password): bool
    {
        if (filled($staff['password_hash'] ?? null) && Hash::check($password, $staff['password_hash'])) {
            return true;
        }

        if (! Schema::hasTable('users')) {
            return false;
        }

        $query = User::query();

        if (Schema::hasColumn('users', 'username') && filled($staff['username'] ?? null)) {
            $query->where('username', $staff['username']);
        } else {
            $query->where('email', $staff['email']);
        }

        $user = $query->first() ?? User::query()->where('email', $staff['email'])->first();

        return $user !== null && Hash::check($password, $user->password);
    }

    public function touchLastLogin(string $id): void
    {
        $existing = $this->user($id);

        if ($existing === null) {
            return;
        }

        $existing['last_login'] = now()->format('Y-m-d H:i');
        $this->writeStaff($id, $existing);
    }

    /**
     * @param  array{first_name: string, last_name: string, email: string, phone?: string|null, role: string, status: string, abilities?: list<string>, password?: string|null}  $data
     * @return array<string, mixed>
     */
    public function createUser(array $data): array
    {
        $base = Str::slug($data['first_name'].' '.$data['last_name']);
        $id = $base === '' ? 'user' : $base;
        $suffix = 2;

        while ($this->user($id) !== null) {
            $id = $base.'-'.$suffix;
            $suffix++;
        }

        $record = $this->staffRecord($id, $data);
        $this->writeStaff($id, $record);
        (new DatabaseRecords)->saveStaff($record, $data['password'] ?? null);

        return $record;
    }

    /**
     * @param  array{first_name: string, last_name: string, email: string, phone?: string|null, role: string, status: string, abilities?: list<string>, password?: string|null}  $data
     * @return array<string, mixed>
     */
    public function updateUser(string $id, array $data): array
    {
        $existing = $this->user($id);

        if ($existing === null) {
            abort(404);
        }

        $record = $this->staffRecord($id, $data, $existing);
        $this->writeStaff($id, $record);
        (new DatabaseRecords)->saveStaff($record, $data['password'] ?? null);

        return $record;
    }

    /**
     * @return Collection<int, array{id: string, name: string, builtin: bool, abilities: list<string>}>
     */
    public function roles(): Collection
    {
        $roles = collect($this->builtinRoles())->keyBy('id');

        foreach (session('admin.roles', []) as $id => $row) {
            $key = (string) $id;

            if (StaffRole::tryFrom($key) !== null) {
                continue;
            }

            $roles->put($key, [
                ...($roles->get($key) ?? []),
                ...$row,
                'id' => $key,
                'builtin' => false,
            ]);
        }

        return $roles->values();
    }

    /**
     * @return array{id: string, name: string, builtin: bool, abilities: list<string>}|null
     */
    public function role(string $id): ?array
    {
        return $this->roles()->firstWhere('id', $id);
    }

    /**
     * @param  array{name: string, abilities?: list<string>}  $data
     * @return array{id: string, name: string, builtin: bool, abilities: list<string>}
     */
    public function createRole(array $data): array
    {
        $base = Str::slug($data['name']);
        $id = $base === '' ? 'role' : $base;
        $suffix = 2;

        while ($this->role($id) !== null) {
            $id = ($base === '' ? 'role' : $base).'-'.$suffix;
            $suffix++;
        }

        $record = $this->customRoleRecord($id, $data);
        $this->writeRole($id, $record);

        return $record;
    }

    /**
     * @param  list<string>  $abilities
     * @return array{id: string, name: string, builtin: bool, abilities: list<string>}
     */
    public function resolveRole(string $name, array $abilities = []): array
    {
        $needle = Str::lower(trim($name));

        $existing = $this->roles()->first(function (array $role) use ($needle): bool {
            return Str::lower($role['name']) === $needle || Str::lower($role['id']) === $needle;
        });

        if ($existing !== null) {
            return $existing;
        }

        return $this->createRole([
            'name' => trim($name),
            'abilities' => $abilities,
        ]);
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
     * @param  array{search?: string|null, user?: string|null, module?: string|null, status?: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function auditLogs(array $filters = []): Collection
    {
        $rows = collect($this->auditCatalog());
        $search = Str::lower(trim((string) ($filters['search'] ?? '')));

        if ($search !== '') {
            $rows = $rows->filter(function (array $row) use ($search): bool {
                return Str::contains(Str::lower($row['user'].' '.$row['action'].' '.$row['module'].' '.$row['reference'].' '.$row['endpoint']), $search);
            });
        }

        foreach (['user', 'module', 'status'] as $key) {
            if (filled($filters[$key] ?? null)) {
                $rows = $rows->where($key, $filters[$key]);
            }
        }

        return $rows->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function auditLog(string $id): ?array
    {
        return collect($this->auditCatalog())->firstWhere('id', $id);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function notifications(): Collection
    {
        $read = collect(session('admin.notifications.read', []));

        return collect($this->notificationCatalog())
            ->map(function (array $item) use ($read): array {
                $unread = $item['unread'] && ! $read->contains($item['id']);

                return [
                    ...$item,
                    'unread' => $unread,
                ];
            })
            ->values();
    }

    public function markNotificationRead(string $id): void
    {
        abort_if(collect($this->notificationCatalog())->firstWhere('id', $id) === null, 404);

        $read = collect(session('admin.notifications.read', []));

        session(['admin.notifications.read' => $read->push($id)->unique()->values()->all()]);
    }

    public function markAllNotificationsRead(): void
    {
        session([
            'admin.notifications.read' => collect($this->notificationCatalog())->pluck('id')->all(),
        ]);
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function settingCategories(): array
    {
        return collect(['general', 'store', 'sales', 'inventory', 'notifications', 'security', 'system'])
            ->map(fn (string $key): array => [
                'key' => $key,
                'label' => __('admin.settings.categories.'.$key),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return [
            ...$this->defaultSettings(),
            ...session('admin.settings', []),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateSettings(array $data): array
    {
        $allowed = array_keys($this->defaultSettings());
        $current = $this->settings();

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed, true)) {
                $current[$key] = $value;
            }
        }

        session(['admin.settings' => $current]);

        return $current;
    }

    /**
     * @return array{name: string, email: string, phone: string, role: string, role_key: string, abilities: list<string>}
     */
    public function currentProfile(AdminStaff $staff): array
    {
        $user = $this->users()->firstWhere('email', $staff->email);

        return [
            'name' => $staff->name,
            'email' => $staff->email,
            'phone' => $staff->phone !== '' ? $staff->phone : (string) ($user['phone'] ?? ''),
            'role' => $staff->role->label(),
            'role_key' => $staff->role->value,
            'abilities' => $staff->role->assignedOperations(),
        ];
    }

    /**
     * @return array<string, list<array{id: string, label: string, meta: string, url: string}>>
     */
    public function search(string $query, AdminStaff $staff): array
    {
        $needle = Str::lower(trim($query));
        $groups = [
            'products' => [],
            'sales' => [],
            'customers' => [],
            'suppliers' => [],
            'users' => [],
        ];

        if ($needle === '') {
            return $groups;
        }

        if ($staff->role->can('products') || $staff->role->can('sales')) {
            $matchedSales = $this->sales()->filter(function (array $sale) use ($needle): bool {
                return Str::contains(Str::lower($sale['number'].' '.$sale['customer'].' '.$sale['items_label']), $needle);
            });

            if ($staff->role->can('sales')) {
                foreach ($matchedSales as $sale) {
                    $groups['sales'][] = [
                        'id' => $sale['id'],
                        'label' => $sale['number'],
                        'meta' => $sale['customer'],
                        'url' => route('admin.sales.show', $sale['id']),
                    ];
                }
            }

            if ($staff->role->can('products')) {
                $products = $this->products()
                    ->filter(function (array $product) use ($needle): bool {
                        return Str::contains(Str::lower($product['name'].' '.$product['sku'].' '.$product['barcode']), $needle);
                    })
                    ->keyBy('slug');

                foreach ($matchedSales as $sale) {
                    foreach ($sale['items'] as $item) {
                        $related = $this->products()->firstWhere('name', $item['product']);

                        if ($related !== null) {
                            $products->put($related['slug'], $related);
                        }
                    }
                }

                $groups['products'] = $products->values()->map(fn (array $product): array => [
                    'id' => $product['slug'],
                    'label' => $product['name'],
                    'meta' => $product['sku'],
                    'url' => route('admin.products.edit', $product['slug']),
                ])->all();
            }
        }

        if ($staff->role->can('customers')) {
            $groups['customers'] = $this->customers()
                ->filter(fn (array $customer): bool => Str::contains(Str::lower($customer['name'].' '.$customer['email'].' '.$customer['phone']), $needle))
                ->map(fn (array $customer): array => [
                    'id' => $customer['id'],
                    'label' => $customer['name'],
                    'meta' => $customer['email'],
                    'url' => route('admin.customers.show', $customer['id']),
                ])
                ->values()
                ->all();
        }

        if ($staff->role->can('suppliers')) {
            $groups['suppliers'] = $this->suppliers()
                ->filter(fn (array $supplier): bool => Str::contains(Str::lower($supplier['name'].' '.$supplier['contact'].' '.$supplier['email']), $needle))
                ->map(fn (array $supplier): array => [
                    'id' => $supplier['id'],
                    'label' => $supplier['name'],
                    'meta' => $supplier['contact'],
                    'url' => route('admin.suppliers.show', $supplier['id']),
                ])
                ->values()
                ->all();
        }

        if ($staff->role->can('users') || $staff->role->can('roles')) {
            $groups['users'] = $this->users()
                ->filter(fn (array $user): bool => Str::contains(Str::lower($user['name'].' '.$user['email']), $needle))
                ->map(fn (array $user): array => [
                    'id' => $user['id'],
                    'label' => $user['name'],
                    'meta' => $user['email'],
                    'url' => route('admin.users.edit', $user['id']),
                ])
                ->values()
                ->all();
        }

        return $groups;
    }

    /**
     * @param  array{label: string, key: string}  $meta
     * @param  array{range?: string|null, from?: string|null, to?: string|null}  $filters
     * @return array<string, mixed>
     */
    private function salesReport(array $meta, array $filters): array
    {
        [$from, $to, $range] = $this->reportPeriod($filters);
        $sales = $this->sales(['from' => $from, 'to' => $to, 'status' => 'completed']);
        $totalSales = (int) $sales->sum('total');
        $orders = $sales->count();
        $itemsSold = (int) $sales->sum(fn (array $sale): int => (int) collect($sale['items'])->sum('qty'));
        $average = $orders > 0 ? (int) round($totalSales / $orders) : 0;
        $products = $this->aggregateSoldProducts($sales);
        $maxQty = max(1, (int) collect($products)->max('quantity'));
        $payments = collect(['cash', 'card', 'other'])->map(function (string $method) use ($sales, $totalSales): array {
            $amount = (int) $sales->where('payment', $method)->sum('total');
            $share = $totalSales > 0 ? (int) round(($amount / $totalSales) * 100) : 0;

            return [
                'key' => $method,
                'label' => __('admin.pos.'.$method),
                'amount' => $amount,
                'share' => $share,
            ];
        })->all();

        return [
            'key' => 'sales',
            'title' => $meta['label'],
            'range' => $range,
            'from' => $from,
            'to' => $to,
            'metrics' => [
                ['key' => 'total_sales', 'label' => __('admin.reports.metrics.total_sales'), 'value' => self::money($totalSales)],
                ['key' => 'total_orders', 'label' => __('admin.reports.metrics.total_orders'), 'value' => (string) $orders],
                ['key' => 'total_items', 'label' => __('admin.reports.metrics.total_items'), 'value' => (string) $itemsSold],
                ['key' => 'average_order', 'label' => __('admin.reports.metrics.average_order'), 'value' => self::money($average)],
            ],
            'trend' => $this->salesTrend($sales, $from, $to, $range),
            'payments' => $payments,
            'top_products' => collect($products)
                ->sortByDesc('quantity')
                ->take(5)
                ->values()
                ->map(fn (array $row): array => [
                    ...$row,
                    'share' => (int) round(($row['quantity'] / $maxQty) * 100),
                ])
                ->all(),
            'headers' => [
                __('admin.reports.table.product'),
                __('admin.reports.table.quantity'),
                __('admin.reports.table.revenue'),
                __('admin.reports.table.discount'),
                __('admin.reports.table.net_sales'),
            ],
            'table' => collect($products)
                ->sortByDesc('quantity')
                ->values()
                ->map(fn (array $row): array => [
                    $row['product'],
                    (string) $row['quantity'],
                    self::money($row['revenue']),
                    self::money($row['discount']),
                    self::money($row['net']),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array{label: string}  $meta
     * @return array<string, mixed>
     */
    private function productsReport(array $meta): array
    {
        $products = $this->aggregateSoldProducts($this->sales(['status' => 'completed']));

        return [
            'key' => 'products',
            'title' => $meta['label'],
            'metrics' => collect($products)
                ->sortByDesc('quantity')
                ->take(3)
                ->values()
                ->map(fn (array $row): array => [
                    'key' => Str::slug($row['product']),
                    'label' => $row['product'],
                    'value' => $row['quantity'].' '.__('admin.dashboard.charts.units'),
                ])
                ->all(),
            'headers' => [
                __('admin.reports.table.product'),
                __('admin.reports.table.quantity'),
                __('admin.reports.table.net_sales'),
            ],
            'table' => collect($products)
                ->sortByDesc('quantity')
                ->values()
                ->map(fn (array $row): array => [
                    $row['product'],
                    (string) $row['quantity'],
                    self::money($row['net']),
                ])
                ->all(),
        ];
    }

    /**
     * @param  array{label: string}  $meta
     * @param  array{category?: string|null, brand?: string|null, stock?: string|null}  $filters
     * @return array<string, mixed>
     */
    private function inventoryReport(array $meta, array $filters): array
    {
        $rows = $this->inventory([
            'category' => $filters['category'] ?? null,
            'brand' => $filters['brand'] ?? null,
            'stock' => $filters['stock'] ?? null,
        ]);
        $products = $this->filteredProducts([
            'category' => $filters['category'] ?? null,
            'brand' => $filters['brand'] ?? null,
            'stock' => $filters['stock'] ?? null,
        ]);

        return [
            'key' => 'inventory',
            'title' => $meta['label'],
            'filters' => [
                'category' => $filters['category'] ?? '',
                'brand' => $filters['brand'] ?? '',
                'stock' => $filters['stock'] ?? '',
            ],
            'categories' => $this->categories(),
            'brands' => $this->brands(),
            'metrics' => [
                ['key' => 'total_products', 'label' => __('admin.reports.metrics.total_products'), 'value' => (string) $products->count()],
                ['key' => 'total_stock', 'label' => __('admin.reports.metrics.total_stock'), 'value' => (string) $rows->sum('stock')],
                ['key' => 'low_stock', 'label' => __('admin.reports.metrics.low_stock'), 'value' => (string) $products->where('stock_status', 'low_stock')->count()],
                ['key' => 'out_of_stock', 'label' => __('admin.reports.metrics.out_of_stock'), 'value' => (string) $products->where('stock_status', 'out_of_stock')->count()],
            ],
            'headers' => [
                __('admin.reports.table.product'),
                __('admin.reports.table.sku'),
                __('admin.reports.table.variant'),
                __('admin.reports.table.stock'),
                __('admin.reports.table.min_stock'),
                __('admin.reports.table.status'),
                __('admin.reports.table.stock_value'),
            ],
            'table' => $rows->map(fn (array $row): array => [
                $row['product'],
                $row['sku'],
                $row['variant'],
                (string) $row['stock'],
                (string) $row['min_stock'],
                __('admin.stock.'.$row['status']),
                self::money($row['stock_value']),
            ])->all(),
            'status_rows' => $rows,
        ];
    }

    /**
     * @param  array{label: string}  $meta
     * @param  array{date?: string|null, from?: string|null, to?: string|null}  $filters
     * @return array<string, mixed>
     */
    private function cashReport(array $meta, array $filters): array
    {
        $date = filled($filters['date'] ?? null) ? (string) $filters['date'] : now()->toDateString();
        $from = filled($filters['from'] ?? null) ? (string) $filters['from'] : $date;
        $to = filled($filters['to'] ?? null) ? (string) $filters['to'] : $date;
        $movements = collect($this->cashMovements())
            ->filter(function (array $row) use ($from, $to): bool {
                $day = Str::substr($row['date'], 0, 10);

                return $day >= $from && $day <= $to;
            })
            ->values();
        $opening = (int) $movements->where('type', 'opening')->sum('amount');
        $sales = (int) $movements->where('type', 'sale')->sum('amount');
        $income = (int) $movements->where('type', 'income')->sum('amount');
        $expenses = (int) abs($movements->where('type', 'expense')->sum('amount'));
        $refunds = (int) abs($movements->where('type', 'refund')->sum('amount'));
        $closing = $opening + $sales + $income - $expenses - $refunds;

        return [
            'key' => 'cash',
            'title' => $meta['label'],
            'date' => $date,
            'from' => $from,
            'to' => $to,
            'metrics' => [
                ['key' => 'opening', 'label' => __('admin.reports.metrics.opening'), 'value' => self::money($opening)],
                ['key' => 'sales', 'label' => __('admin.reports.metrics.cash_sales'), 'value' => self::money($sales)],
                ['key' => 'income', 'label' => __('admin.reports.metrics.income'), 'value' => self::money($income)],
                ['key' => 'expenses', 'label' => __('admin.reports.metrics.expenses'), 'value' => self::money($expenses)],
                ['key' => 'refunds', 'label' => __('admin.reports.metrics.refunds'), 'value' => self::money($refunds)],
                ['key' => 'closing', 'label' => __('admin.reports.metrics.closing'), 'value' => self::money($closing)],
            ],
            'trend' => $movements->map(fn (array $row): array => [
                'label' => Str::substr($row['date'], 11, 5) !== '' ? Str::substr($row['date'], 11, 5) : Str::substr($row['date'], 5, 5),
                'value' => abs((int) $row['amount']),
            ])->all(),
            'headers' => [
                __('admin.cash.date'),
                __('admin.cash.type'),
                __('admin.cash.description'),
                __('admin.cash.reference'),
                __('admin.cash.amount'),
                __('admin.cash.balance'),
            ],
            'table' => $movements->map(fn (array $row): array => [
                $row['date'],
                __('admin.status.'.$row['type']),
                $row['description'],
                $row['reference'],
                self::money($row['amount']),
                self::money($row['balance']),
            ])->all(),
        ];
    }

    /**
     * @param  array{label: string}  $meta
     * @return array<string, mixed>
     */
    private function returnReport(array $meta): array
    {
        $returns = $this->returns();
        $sales = $this->sales();
        $totalReturns = $returns->count();
        $returnValue = (int) $returns->sum('amount');
        $returnRate = $sales->count() > 0
            ? round(($totalReturns / $sales->count()) * 100, 1)
            : 0.0;
        $soldByProduct = $this->aggregateSoldProducts($sales);
        $returned = [];

        foreach ($returns as $row) {
            $name = Str::of($row['products'])->before(' / ')->trim()->toString();
            $returned[$name] ??= ['product' => $name, 'quantity' => 0, 'value' => 0, 'reasons' => []];
            $returned[$name]['quantity']++;
            $returned[$name]['value'] += $row['amount'];
            $returned[$name]['reasons'][] = $row['reason'];
        }

        $table = collect($returned)
            ->map(function (array $row) use ($soldByProduct): array {
                $sold = (int) (collect($soldByProduct)->firstWhere('product', $row['product'])['quantity'] ?? 0);
                $reasons = collect($row['reasons'])->countBy()->sortDesc();
                $main = $reasons->keys()->first() ?? 'other';

                return [
                    'product' => $row['product'],
                    'quantity' => $row['quantity'],
                    'value' => $row['value'],
                    'rate' => $sold > 0 ? round(($row['quantity'] / $sold) * 100, 1) : 0.0,
                    'reason' => $main,
                ];
            })
            ->sortByDesc('quantity')
            ->values();
        $top = $table->first();

        return [
            'key' => 'returns',
            'title' => $meta['label'],
            'metrics' => [
                ['key' => 'total_returns', 'label' => __('admin.reports.metrics.total_returns'), 'value' => (string) $totalReturns],
                ['key' => 'return_rate', 'label' => __('admin.reports.metrics.return_rate'), 'value' => number_format($returnRate, 1).'%'],
                ['key' => 'return_value', 'label' => __('admin.reports.metrics.return_value'), 'value' => self::money($returnValue)],
                ['key' => 'most_returned', 'label' => __('admin.reports.metrics.most_returned'), 'value' => $top['product'] ?? '—'],
            ],
            'headers' => [
                __('admin.reports.table.product'),
                __('admin.reports.table.return_quantity'),
                __('admin.reports.table.return_value'),
                __('admin.reports.table.return_rate'),
                __('admin.reports.table.main_reason'),
            ],
            'table' => $table->map(fn (array $row): array => [
                $row['product'],
                (string) $row['quantity'],
                self::money($row['value']),
                number_format($row['rate'], 1).'%',
                __('admin.status.'.$row['reason']),
            ])->all(),
        ];
    }

    /**
     * @param  array{label: string}  $meta
     * @return array<string, mixed>
     */
    private function customerReport(array $meta): array
    {
        $customers = $this->customers();
        $monthStart = now()->startOfMonth()->toDateString();
        $activeSince = now()->subDays(30)->toDateString();
        $totalSales = (int) $customers->sum('spent');
        $count = $customers->count();
        $average = $count > 0 ? (int) round($totalSales / $count) : 0;
        $top = $customers->sortByDesc('spent')->values();

        return [
            'key' => 'customers',
            'title' => $meta['label'],
            'metrics' => [
                ['key' => 'total_customers', 'label' => __('admin.reports.metrics.total_customers'), 'value' => (string) $count],
                ['key' => 'new_customers', 'label' => __('admin.reports.metrics.new_customers'), 'value' => (string) $customers->filter(fn (array $row): bool => $row['created_at'] >= $monthStart)->count()],
                ['key' => 'active_customers', 'label' => __('admin.reports.metrics.active_customers'), 'value' => (string) $customers->filter(fn (array $row): bool => $row['last_purchase'] >= $activeSince)->count()],
                ['key' => 'customer_sales', 'label' => __('admin.reports.metrics.customer_sales'), 'value' => self::money($totalSales)],
                ['key' => 'average_value', 'label' => __('admin.reports.metrics.average_value'), 'value' => self::money($average)],
            ],
            'headers' => [
                __('admin.customers.name'),
                __('admin.customers.orders'),
                __('admin.customers.spent'),
                __('admin.customers.last_purchase'),
            ],
            'table' => $top->map(fn (array $row): array => [
                $row['name'],
                (string) $row['orders'],
                self::money($row['spent']),
                $row['last_purchase'],
            ])->all(),
        ];
    }

    /**
     * @param  array{label: string}  $meta
     * @return array<string, mixed>
     */
    private function supplierReport(array $meta): array
    {
        $suppliers = $this->suppliers();
        $totalPurchases = (int) $suppliers->sum('total');
        $max = max(1, $totalPurchases);
        $top = $suppliers->sortByDesc('total')->values();

        return [
            'key' => 'suppliers',
            'title' => $meta['label'],
            'metrics' => [
                ['key' => 'total_suppliers', 'label' => __('admin.reports.metrics.total_suppliers'), 'value' => (string) $suppliers->count()],
                ['key' => 'total_purchases', 'label' => __('admin.reports.metrics.total_purchases'), 'value' => self::money($totalPurchases)],
                ['key' => 'top_suppliers', 'label' => __('admin.reports.metrics.top_suppliers'), 'value' => $top->first()['name'] ?? '—'],
            ],
            'top_suppliers' => $top->map(fn (array $row): array => [
                'name' => $row['name'],
                'amount' => $row['total'],
                'share' => (int) round(($row['total'] / $max) * 100),
            ])->all(),
            'headers' => [
                __('admin.suppliers.supplier'),
                __('admin.suppliers.purchases'),
                __('admin.suppliers.total'),
                __('admin.suppliers.last_purchase'),
            ],
            'table' => $top->map(fn (array $row): array => [
                $row['name'],
                (string) $row['purchases'],
                self::money($row['total']),
                $row['last_purchase'],
            ])->all(),
        ];
    }

    /**
     * @param  array{range?: string|null, from?: string|null, to?: string|null}  $filters
     * @return array{0: string, 1: string, 2: string}
     */
    private function reportPeriod(array $filters): array
    {
        $range = (string) ($filters['range'] ?? 'today');

        if (! in_array($range, ['today', 'week', 'month', 'custom'], true)) {
            $range = 'today';
        }

        $today = now()->toDateString();

        return match ($range) {
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString(), $range],
            'month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), $range],
            'custom' => [
                filled($filters['from'] ?? null) ? (string) $filters['from'] : $today,
                filled($filters['to'] ?? null) ? (string) $filters['to'] : $today,
                $range,
            ],
            default => [$today, $today, 'today'],
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $sales
     * @return list<array{label: string, value: int}>
     */
    private function salesTrend(Collection $sales, string $from, string $to, string $range): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();
        $grouped = $sales->groupBy(fn (array $sale): string => Str::substr($sale['date'], 0, 10));

        if ($range === 'month' && $start->diffInDays($end) > 14) {
            $points = [];
            $cursor = $start->copy()->startOfWeek();

            while ($cursor->lte($end)) {
                $weekEnd = $cursor->copy()->endOfWeek();
                $amount = $grouped
                    ->filter(fn ($_, string $day): bool => $day >= $cursor->toDateString() && $day <= $weekEnd->toDateString())
                    ->sum(fn (Collection $rows): int => (int) $rows->sum('total'));
                $points[] = ['label' => $cursor->format('M j'), 'value' => $amount];
                $cursor->addWeek();
            }

            return $points;
        }

        $points = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $day = $cursor->toDateString();
            $points[] = [
                'label' => $cursor->format('M j'),
                'value' => (int) ($grouped->get($day)?->sum('total') ?? 0),
            ];
            $cursor->addDay();
        }

        return $points;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $sales
     * @return list<array{product: string, quantity: int, revenue: int, discount: int, net: int}>
     */
    private function aggregateSoldProducts(Collection $sales): array
    {
        $products = [];

        foreach ($sales as $sale) {
            foreach ($sale['items'] as $item) {
                $name = $item['product'];
                $products[$name] ??= ['product' => $name, 'quantity' => 0, 'revenue' => 0, 'discount' => 0, 'net' => 0];
                $products[$name]['quantity'] += (int) $item['qty'];
                $products[$name]['revenue'] += (int) $item['unit'] * (int) $item['qty'];
                $products[$name]['discount'] += (int) ($item['discount'] ?? 0);
                $products[$name]['net'] += (int) $item['total'];
            }
        }

        return array_values($products);
    }

    /**
     * @param  array{first_name: string, last_name: string, email: string, phone?: string|null, role: string, status: string, abilities?: list<string>}  $data
     * @param  array<string, mixed>|null  $existing
     * @return array<string, mixed>
     */
    private function staffRecord(string $id, array $data, ?array $existing = null): array
    {
        $role = $this->role($data['role']);

        if ($role === null) {
            abort(404);
        }

        $abilities = array_values(array_intersect(
            $data['abilities'] ?? [],
            StaffRole::operations(),
        ));

        return [
            'id' => $id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
            'username' => Str::lower(trim((string) ($data['username'] ?? $existing['username'] ?? ''))),
            'phone' => (string) ($data['phone'] ?? ''),
            'role' => $role['id'],
            'abilities' => $abilities,
            'status' => $data['status'],
            'password_hash' => filled($data['password'] ?? null)
                ? Hash::make((string) $data['password'])
                : ($existing['password_hash'] ?? null),
            'last_login' => $existing['last_login'] ?? '—',
            'created_at' => $existing['created_at'] ?? now()->toDateString(),
        ];
    }

    /**
     * @return list<array{id: string, name: string, builtin: bool, abilities: list<string>}>
     */
    private function builtinRoles(): array
    {
        return collect(StaffRole::cases())
            ->map(fn (StaffRole $role): array => [
                'id' => $role->value,
                'name' => $role->label(),
                'builtin' => true,
                'abilities' => $role->assignedOperations(),
            ])
            ->all();
    }

    /**
     * @param  array{name: string, abilities?: list<string>}  $data
     * @return array{id: string, name: string, builtin: bool, abilities: list<string>}
     */
    private function customRoleRecord(string $id, array $data): array
    {
        return [
            'id' => $id,
            'name' => $data['name'],
            'builtin' => false,
            'abilities' => array_values(array_intersect(
                $data['abilities'] ?? [],
                StaffRole::operations(),
            )),
        ];
    }

    /**
     * @param  array{id: string, name: string, builtin: bool, abilities: list<string>}  $record
     */
    private function writeRole(string $id, array $record): void
    {
        $roles = session('admin.roles', []);
        $roles[$id] = $record;
        session(['admin.roles' => $roles]);
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function writeStaff(string $id, array $record): void
    {
        $staff = session('admin.staff', []);
        $staff[$id] = $record;
        session(['admin.staff' => $staff]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function customerCatalog(): array
    {
        return [
            ['id' => 'elif-kaya', 'name' => 'Elif Kaya', 'phone' => '0532 441 12 08', 'email' => 'elif.kaya@email.com', 'orders' => 14, 'spent' => 24800, 'last_purchase' => '2026-09-02', 'city' => 'Istanbul', 'created_at' => '2025-03-12'],
            ['id' => 'mert-aydin', 'name' => 'Mert Aydın', 'phone' => '0533 210 88 41', 'email' => 'mert.aydin@email.com', 'orders' => 6, 'spent' => 9720, 'last_purchase' => '2026-09-01', 'city' => 'Ankara', 'created_at' => '2026-08-04'],
            ['id' => 'selin-arslan', 'name' => 'Selin Arslan', 'phone' => '0542 118 03 76', 'email' => 'selin.arslan@email.com', 'orders' => 21, 'spent' => 41250, 'last_purchase' => '2026-08-30', 'city' => 'Izmir', 'created_at' => '2024-11-18'],
            ['id' => 'can-demir', 'name' => 'Can Demir', 'phone' => '0505 667 91 20', 'email' => 'can.demir@email.com', 'orders' => 3, 'spent' => 3180, 'last_purchase' => '2026-08-22', 'city' => 'Bursa', 'created_at' => '2026-09-01'],
            ['id' => 'deniz-yildiz', 'name' => 'Deniz Yıldız', 'phone' => '0536 904 55 12', 'email' => 'deniz.yildiz@email.com', 'orders' => 9, 'spent' => 15640, 'last_purchase' => '2026-08-18', 'city' => 'Istanbul', 'created_at' => '2026-01-20'],
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function databaseCustomers(): Collection
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'slug')) {
            return collect();
        }

        return Customer::query()
            ->withCount('orders')
            ->orderBy('first_name')
            ->get()
            ->map(function (Customer $customer): array {
                return [
                    'id' => $customer->slug ?: $customer->id,
                    'name' => trim($customer->first_name.' '.$customer->last_name),
                    'phone' => (string) $customer->phone,
                    'email' => (string) $customer->email,
                    'orders' => (int) $customer->orders_count,
                    'spent' => 0,
                    'last_purchase' => $customer->updated_at?->toDateString() ?? '',
                    'city' => '',
                    'created_at' => $customer->created_at?->toDateString() ?? '',
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function databaseSuppliers(): Collection
    {
        if (! Schema::hasTable('suppliers') || ! Schema::hasColumn('suppliers', 'slug')) {
            return collect();
        }

        return Supplier::query()
            ->orderBy('company_name')
            ->get()
            ->map(function (Supplier $supplier): array {
                return [
                    'id' => $supplier->slug ?: $supplier->id,
                    'name' => $supplier->company_name,
                    'contact' => (string) $supplier->contact_name,
                    'phone' => (string) $supplier->phone,
                    'email' => (string) $supplier->email,
                    'address' => (string) $supplier->address,
                    'tax' => (string) $supplier->tax_number,
                    'purchases' => 0,
                    'total' => 0,
                    'last_purchase' => '',
                    'status' => $supplier->is_active ? 'active' : 'inactive',
                    'balance' => 0,
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function databaseUsers(): Collection
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'slug') || ! Schema::hasTable('roles')) {
            return collect();
        }

        return User::query()
            ->with('roles')
            ->whereHas('roles')
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                $parts = preg_split('/\s+/', trim($user->name), 2) ?: [];
                $role = $user->roles->first();

                return [
                    'id' => $user->slug ?: Str::slug($user->name),
                    'first_name' => $parts[0] ?? $user->name,
                    'last_name' => $parts[1] ?? '',
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => (string) $user->username,
                    'phone' => (string) $user->phone,
                    'role' => $role?->slug ?? StaffRole::Cashier->value,
                    'abilities' => [],
                    'status' => $user->is_active ? 'active' : 'inactive',
                    'last_login' => '—',
                    'created_at' => $user->created_at?->toDateString() ?? '',
                ];
            })
            ->filter(fn (array $row): bool => filled($row['id']))
            ->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function userCatalog(): array
    {
        return [
            [
                'id' => 'ayse-yilmaz',
                'first_name' => 'Ayşe',
                'last_name' => 'Yılmaz',
                'name' => 'Ayşe Yılmaz',
                'email' => 'ayse.yilmaz@nova.store',
                'username' => 'ayse',
                'phone' => '0532 441 00 11',
                'role' => StaffRole::Cashier->value,
                'abilities' => StaffRole::Cashier->assignedOperations(),
                'status' => 'active',
                'last_login' => '2026-09-02 09:14',
                'created_at' => '2025-04-10',
            ],
            [
                'id' => 'mert-kaya',
                'first_name' => 'Mert',
                'last_name' => 'Kaya',
                'name' => 'Mert Kaya',
                'email' => 'mert.kaya@nova.store',
                'username' => 'mert',
                'phone' => '0533 220 44 18',
                'role' => StaffRole::Cashier->value,
                'abilities' => StaffRole::Cashier->assignedOperations(),
                'status' => 'active',
                'last_login' => '2026-09-01 18:20',
                'created_at' => '2025-06-02',
            ],
            [
                'id' => 'deniz-aksoy',
                'first_name' => 'Deniz',
                'last_name' => 'Aksoy',
                'name' => 'Deniz Aksoy',
                'email' => 'deniz.aksoy@nova.store',
                'username' => 'deniz',
                'phone' => '0536 118 90 22',
                'role' => StaffRole::StoreManager->value,
                'abilities' => StaffRole::StoreManager->assignedOperations(),
                'status' => 'active',
                'last_login' => '2026-09-01 11:40',
                'created_at' => '2024-11-08',
            ],
            [
                'id' => 'ece-yilmaz',
                'first_name' => 'Ece',
                'last_name' => 'Yılmaz',
                'name' => 'Ece Yılmaz',
                'email' => 'ece.yilmaz@nova.store',
                'username' => 'ece',
                'phone' => '0542 667 31 09',
                'role' => StaffRole::WarehouseStaff->value,
                'abilities' => StaffRole::WarehouseStaff->assignedOperations(),
                'status' => 'active',
                'last_login' => '2026-08-30 10:02',
                'created_at' => '2026-02-14',
            ],
            [
                'id' => 'admin',
                'first_name' => 'NOVA',
                'last_name' => 'Admin',
                'name' => 'NOVA Admin',
                'email' => 'admin@nova.store',
                'username' => 'novaadmin',
                'phone' => '0212 000 00 01',
                'role' => StaffRole::SuperAdmin->value,
                'abilities' => StaffRole::SuperAdmin->assignedOperations(),
                'status' => 'inactive',
                'last_login' => '2026-08-12 16:05',
                'created_at' => '2024-01-01',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultSettings(): array
    {
        return [
            'store_name' => 'NOVA',
            'store_email' => 'hello@nova.store',
            'phone' => '0212 000 00 01',
            'address' => 'Nişantaşı, Istanbul',
            'currency' => 'TRY',
            'store_info' => 'Contemporary ready-to-wear retail.',
            'opening_hours' => '10:00–20:00',
            'default_language' => 'en',
            'default_discount' => 0,
            'payment_cash' => true,
            'payment_card' => true,
            'receipt_footer' => 'Thank you for shopping at NOVA.',
            'low_stock_threshold' => 8,
            'allow_negative_stock' => false,
            'notify_low_stock' => true,
            'notify_sales' => true,
            'notify_returns' => true,
            'session_timeout' => 120,
            'password_min' => 8,
            'login_protection' => true,
            'timezone' => 'Europe/Istanbul',
            'api_url' => 'https://api.nova.store',
            'api_status' => 'operational',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function notificationCatalog(): array
    {
        return [
            [
                'id' => 'low-stock-basic',
                'title' => __('admin.notifications.examples.low_stock'),
                'body' => 'SKU NOVA01 · White / M',
                'time' => '12m',
                'unread' => true,
            ],
            [
                'id' => 'return-created',
                'title' => __('admin.notifications.examples.return'),
                'body' => 'RT-2204 · Elif Kaya',
                'time' => '38m',
                'unread' => true,
            ],
            [
                'id' => 'cash-close',
                'title' => __('admin.notifications.examples.cash'),
                'body' => __('admin.notifications.examples.cash_body'),
                'time' => '1h',
                'unread' => true,
            ],
            [
                'id' => 'sale-completed',
                'title' => __('admin.notifications.examples.sale'),
                'body' => 'NOVA-1024 · ₺2,398',
                'time' => '2h',
                'unread' => false,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function auditCatalog(): array
    {
        return [
            [
                'id' => 'aud-1024',
                'datetime' => '2026-09-02 09:42',
                'user' => 'Admin',
                'action' => 'Updated Product',
                'module' => 'Products',
                'reference' => '#NOVA-1024',
                'ip' => '192.168.1.xxx',
                'endpoint' => 'PUT /api/products/1024',
                'status' => 'success',
                'old' => ['price' => '₺849', 'status' => 'draft'],
                'new' => ['price' => '₺899', 'status' => 'active'],
            ],
            [
                'id' => 'aud-10482',
                'datetime' => '2026-09-02 09:14',
                'user' => 'Ayşe Yılmaz',
                'action' => 'Created Sale',
                'module' => 'Sales',
                'reference' => '#NOVA-1024',
                'ip' => '192.168.1.xxx',
                'endpoint' => 'POST /api/sales',
                'status' => 'success',
                'old' => null,
                'new' => ['total' => '₺2,398', 'payment' => 'cash'],
            ],
            [
                'id' => 'aud-2204',
                'datetime' => '2026-09-01 18:20',
                'user' => 'Mert Kaya',
                'action' => 'Created Return',
                'module' => 'Returns',
                'reference' => '#RT-2204',
                'ip' => '192.168.1.xxx',
                'endpoint' => 'POST /api/returns',
                'status' => 'success',
                'old' => null,
                'new' => ['amount' => '₺449', 'reason' => 'wrong_size'],
            ],
            [
                'id' => 'aud-close',
                'datetime' => '2026-09-01 21:05',
                'user' => 'Mert Kaya',
                'action' => 'Closed Register',
                'module' => 'Cash',
                'reference' => '#CR-0901',
                'ip' => '192.168.1.xxx',
                'endpoint' => 'POST /api/cash/close',
                'status' => 'success',
                'old' => ['open' => true],
                'new' => ['open' => false, 'actual' => '₺14,060'],
            ],
            [
                'id' => 'aud-fail',
                'datetime' => '2026-08-30 11:12',
                'user' => 'Deniz Aksoy',
                'action' => 'Adjusted Stock',
                'module' => 'Inventory',
                'reference' => '#ADJ-19',
                'ip' => '10.0.0.xxx',
                'endpoint' => 'POST /api/inventory/adjust',
                'status' => 'failure',
                'old' => ['stock' => 2],
                'new' => ['stock' => 0],
            ],
        ];
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
