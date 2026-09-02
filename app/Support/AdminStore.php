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
            ['date' => '2026-09-02 09:14', 'product' => 'Basic Shirt', 'type' => 'sale', 'qty' => -1, 'before' => 43, 'after' => 42, 'user' => 'Ayşe Yılmaz', 'reference' => 'NV-10482'],
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
