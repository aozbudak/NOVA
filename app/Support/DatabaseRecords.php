<?php

namespace App\Support;

use App\Enums\StaffRole;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnItem;
use App\Models\Role;
use App\Models\SaleReturn;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class DatabaseRecords
{
    public function saveProduct(array $data, ?string $slug = null): ?Product
    {
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '' || ! Schema::hasTable('products')) {
            return null;
        }

        return DB::transaction(function () use ($data, $name, $slug): Product {
            $categoryName = trim((string) ($data['category'] ?? 'Shirts')) ?: 'Shirts';
            $category = Category::query()->firstOrCreate(
                ['slug' => 'admin-'.Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );

            $price = (float) ($data['price'] ?? 0);
            $minStock = (int) ($data['min_stock'] ?? 0);
            $initialStock = (int) ($data['initial_stock'] ?? 0);
            $productSlug = $slug ?? $this->uniqueValue('products', 'slug', Str::slug($name) ?: 'product');
            $sku = trim((string) ($data['sku'] ?? ''));

            $attributes = [
                'channel' => 'admin',
                'sku' => $sku !== '' ? $sku : Str::upper(Str::slug($name, '')),
                'purchase_price' => (float) ($data['purchase_price'] ?? 0),
                'vat' => (int) ($data['vat'] ?? 20),
                'min_stock' => $minStock,
            ];

            $values = [
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $productSlug,
                'description' => $data['description'] ?? null,
                'brand' => filled($data['brand'] ?? null) ? $data['brand'] : 'NOVA',
                'base_price' => $price,
                'sale_price' => null,
                'currency' => 'TRY',
                'is_active' => ($data['status'] ?? 'active') !== 'inactive',
                'catalog_code' => null,
                'attributes' => $attributes,
            ];

            $product = $slug === null
                ? Product::query()->create($values)
                : Product::query()->where('slug', $slug)->first();

            if ($product === null) {
                $product = Product::query()->create($values);
            } else {
                $product->update($values);
            }

            $this->syncVariants($product, $data, $price, $initialStock, $minStock);

            return $product->fresh(['variants.stock']) ?? $product;
        });
    }

    public function deactivateProduct(string $slug): bool
    {
        if (! Schema::hasTable('products')) {
            return false;
        }

        $product = Product::query()->where('slug', $slug)->first();

        if ($product === null) {
            return false;
        }

        $product->update(['is_active' => false]);

        return true;
    }

    /**
     * @param  array{id: string, name: string, email: string, phone?: string|null}  $record
     */
    public function saveCustomer(array $record): ?Customer
    {
        if (! $this->hasSlug('customers')) {
            return null;
        }

        [$firstName, $lastName] = $this->splitName($record['name']);

        return Customer::query()->updateOrCreate(
            ['slug' => $record['id']],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $record['email'],
                'phone' => filled($record['phone'] ?? null) ? $record['phone'] : null,
                'is_active' => true,
            ],
        );
    }

    /**
     * @param  array{id: string, name: string, contact?: string|null, email?: string|null, phone?: string|null, address?: string|null, tax?: string|null, status?: string|null}  $record
     */
    public function saveSupplier(array $record): ?Supplier
    {
        if (! $this->hasSlug('suppliers')) {
            return null;
        }

        return Supplier::query()->updateOrCreate(
            ['slug' => $record['id']],
            [
                'company_name' => $record['name'],
                'contact_name' => $record['contact'] ?? null,
                'email' => $record['email'] ?? null,
                'phone' => filled($record['phone'] ?? null) ? $record['phone'] : null,
                'tax_number' => $record['tax'] ?? null,
                'address' => $record['address'] ?? null,
                'is_active' => ($record['status'] ?? 'active') !== 'inactive',
            ],
        );
    }

    /**
     * @param  array{id: string, name: string, email: string, phone?: string|null, role: string, status: string}  $record
     */
    public function saveStaff(array $record, ?string $password = null): ?User
    {
        if (! $this->hasSlug('users')) {
            return null;
        }

        $user = User::query()->where('slug', $record['id'])->first()
            ?? User::query()->where('email', $record['email'])->first();

        $payload = [
            'slug' => $record['id'],
            'name' => $record['name'],
            'email' => $record['email'],
            'phone' => filled($record['phone'] ?? null) ? $record['phone'] : null,
            'is_active' => ($record['status'] ?? 'active') === 'active',
        ];

        if (filled($password)) {
            $payload['password'] = $password;
        } elseif ($user === null) {
            $payload['password'] = Str::password(16);
        }

        if ($user === null) {
            $user = User::query()->create($payload);
        } else {
            $user->update($payload);
        }

        if (Schema::hasTable('roles')) {
            $roleSlug = (string) $record['role'];
            $role = Role::query()->firstOrCreate(
                ['slug' => $roleSlug],
                [
                    'name' => StaffRole::tryFrom($roleSlug)?->label() ?? Str::headline(str_replace('_', ' ', $roleSlug)),
                ],
            );
            $user->roles()->sync([$role->id => ['created_at' => now()]]);
        }

        return $user;
    }

    public function adjustStock(string $sku, int $quantity, ?string $reason = null): bool
    {
        if (! Schema::hasTable('product_variants') || ! Schema::hasTable('stocks')) {
            return false;
        }

        $variant = ProductVariant::query()->where('sku', $sku)->first();

        if ($variant === null) {
            return false;
        }

        return DB::transaction(function () use ($variant, $quantity, $reason): true {
            $stock = $variant->stock;

            if ($stock === null) {
                $stock = Stock::query()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => max(0, $quantity),
                    'reserved_quantity' => 0,
                    'minimum_quantity' => 5,
                ]);
            } else {
                $stock->update(['quantity' => max(0, (int) $stock->quantity + $quantity)]);
            }

            if (Schema::hasTable('stock_movements')) {
                StockMovement::query()->create([
                    'product_variant_id' => $variant->id,
                    'movement_type' => $quantity >= 0 ? 'in' : 'out',
                    'quantity' => $quantity,
                    'reference_type' => 'adjustment',
                    'note' => $reason,
                ]);
            }

            return true;
        });
    }

    public function openRegister(float $opening = 0): ?CashRegister
    {
        if (! Schema::hasTable('cash_registers')) {
            return null;
        }

        return DB::transaction(function () use ($opening): CashRegister {
            $register = CashRegister::query()->create([
                'name' => 'Main',
                'opening_balance' => $opening,
                'is_active' => true,
                'opened_at' => now(),
                'closed_at' => null,
            ]);

            $this->addCashTransaction($register, 'opening', $opening, 'Opening balance');

            return $register;
        });
    }

    public function closeRegister(?float $actual = null): ?CashRegister
    {
        if (! Schema::hasTable('cash_registers')) {
            return null;
        }

        $register = $this->activeRegister();

        if ($register === null) {
            return null;
        }

        $register->update([
            'is_active' => false,
            'closed_at' => now(),
        ]);

        $this->addCashTransaction($register, 'closing', $actual ?? (float) $register->opening_balance, 'Closing balance');

        return $register;
    }

    public function recordIncomeExpense(string $type, string $category, string $description, float $amount): ?CashTransaction
    {
        if (! Schema::hasTable('cash_transactions')) {
            return null;
        }

        $register = $this->activeRegister() ?? $this->openRegister();

        if ($register === null) {
            return null;
        }

        return $this->addCashTransaction(
            $register,
            $type,
            $amount,
            trim($category.' — '.$description),
        );
    }

    /**
     * @param  array{first_name: string, last_name: string, email: string, password: string}  $data
     */
    public function registerCustomer(array $data): ?Customer
    {
        if (! $this->hasSlug('customers') || ! Schema::hasTable('users')) {
            return null;
        }

        return DB::transaction(function () use ($data): Customer {
            $slug = $this->uniqueValue('users', 'slug', Str::slug($data['first_name'].' '.$data['last_name']) ?: 'customer');

            $payload = [
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
            ];

            if (Schema::hasColumn('users', 'slug')) {
                $payload['slug'] = $slug;
            }

            $user = User::query()->create($payload);

            return Customer::query()->create([
                'user_id' => $user->id,
                'slug' => $this->uniqueValue('customers', 'slug', $slug),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'is_active' => true,
            ]);
        });
    }

    public function authenticateCustomer(string $email, string $password): Customer|false|null
    {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return null;
        }

        if (! Hash::check($password, $user->password)) {
            return false;
        }

        if (! Schema::hasTable('customers')) {
            return null;
        }

        return Customer::query()->where('user_id', $user->id)->first()
            ?? Customer::query()->where('email', $email)->first();
    }

    /**
     * @param  array<string, mixed>  $checkout
     */
    public function placeCheckout(Cart $cart, array $checkout, string $orderNumber): ?Order
    {
        if (! Schema::hasTable('orders')) {
            return null;
        }

        return DB::transaction(function () use ($cart, $checkout, $orderNumber): Order {
            $customer = $this->customerFromCheckout($checkout);
            $items = $cart->items();
            $subtotal = $cart->subtotal();
            $currency = (string) ($items->first()['product']['currency'] ?? 'EUR');

            $address = [
                'first_name' => $checkout['first_name'],
                'last_name' => $checkout['last_name'],
                'address_line' => $checkout['address'],
                'city' => $checkout['city'],
                'postal_code' => $checkout['postal_code'],
                'country' => $checkout['country'],
            ];

            $order = Order::query()->create([
                'order_number' => $orderNumber,
                'customer_id' => $customer?->id,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'shipping_amount' => 0,
                'total_amount' => $subtotal,
                'currency' => $currency,
                'shipping_address' => $address,
                'billing_address' => $address,
                'notes' => $checkout['delivery'].'/'.$checkout['payment'],
            ]);

            foreach ($items as $line) {
                $variant = $this->storefrontVariant((int) $line['product']['id'], (string) $line['size']);
                $quantity = (int) $line['quantity'];
                $unit = (float) $line['product']['price'];

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $line['product']['name'],
                    'sku' => $variant?->sku,
                    'size' => $line['size'],
                    'quantity' => $quantity,
                    'unit_price' => $unit,
                    'total_price' => $line['line_total'],
                ]);

                if ($variant !== null) {
                    $this->moveStock($variant, -$quantity, 'sale', $order->id);
                }
            }

            $this->storeAddress($customer, $checkout);
            $this->recordPayment($order, $checkout['payment'], $subtotal);

            return $order;
        });
    }

    /**
     * @param  array{payment: string, customer_id?: string|null}  $sale
     * @param  Collection<int, array<string, mixed>>  $lines
     */
    public function placeSale(array $sale, Collection $lines, string $number): ?Order
    {
        if (! Schema::hasTable('orders')) {
            return null;
        }

        return DB::transaction(function () use ($sale, $lines, $number): Order {
            $customer = $this->customerBySlug((string) ($sale['customer_id'] ?? ''));
            $subtotal = (float) $lines->sum(fn (array $line): float => $line['unit'] * $line['qty']);
            $discount = (float) $lines->sum('discount');
            $total = (float) $lines->sum('total');

            $order = Order::query()->create([
                'order_number' => $number,
                'customer_id' => $customer?->id,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'currency' => 'TRY',
            ]);

            foreach ($lines as $line) {
                $variant = ProductVariant::query()->where('sku', $line['sku'])->first();
                $parts = explode(' / ', (string) $line['variant']);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant?->id,
                    'product_name' => $line['product'],
                    'sku' => $line['sku'],
                    'color' => $parts[0] ?? null,
                    'size' => $parts[1] ?? null,
                    'quantity' => (int) $line['qty'],
                    'unit_price' => $line['unit'],
                    'discount_amount' => $line['discount'],
                    'total_price' => $line['total'],
                ]);

                if ($variant !== null) {
                    $this->moveStock($variant, -((int) $line['qty']), 'sale', $order->id);
                }
            }

            $this->recordPayment($order, $sale['payment'], $total);

            $register = $this->activeRegister();

            if ($register !== null && $sale['payment'] === 'cash') {
                $this->addCashTransaction($register, 'sale', $total, $number, 'order', $order->id);
            }

            return $order;
        });
    }

    public function completeReturn(string $saleNumber, ?string $reason = null): ?SaleReturn
    {
        if (! Schema::hasTable('returns') || ! Schema::hasTable('orders')) {
            return null;
        }

        $order = Order::query()->where('order_number', $saleNumber)->with('items.variant.stock')->first();

        if ($order === null) {
            return null;
        }

        return DB::transaction(function () use ($order, $reason): SaleReturn {
            $return = SaleReturn::query()->create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'return_number' => 'RT-'.now()->format('ymdHis'),
                'reason' => $reason,
                'status' => 'completed',
                'total_amount' => $order->total_amount,
                'approved_at' => now(),
                'completed_at' => now(),
            ]);

            foreach ($order->items as $item) {
                ReturnItem::query()->create([
                    'return_id' => $return->id,
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'reason' => $reason,
                ]);

                if ($item->variant !== null) {
                    $this->moveStock($item->variant, (int) $item->quantity, 'return', $return->id);
                }
            }

            $order->update(['status' => 'returned']);

            return $return;
        });
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null}  $data
     */
    public function updateProfile(string $email, array $data): ?User
    {
        if (! Schema::hasTable('users')) {
            return null;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return null;
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
        ]);

        return $user;
    }

    public function updatePassword(string $email, string $current, string $password): bool
    {
        if (! Schema::hasTable('users')) {
            return true;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return true;
        }

        if (! Hash::check($current, $user->password)) {
            return false;
        }

        $user->update(['password' => $password]);

        return true;
    }

    private function hasSlug(string $table): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, 'slug');
    }

    private function uniqueValue(string $table, string $column, string $base): string
    {
        $value = $base === '' ? 'item' : $base;
        $original = $value;
        $suffix = 2;

        while (DB::table($table)->where($column, $value)->exists()) {
            $value = $original.'-'.$suffix;
            $suffix++;
        }

        return $value;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?: $name, $parts[1] ?? ''];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncVariants(Product $product, array $data, float $fallbackPrice, int $fallbackStock, int $minStock): void
    {
        foreach ($this->variantRows($data, $fallbackPrice, $fallbackStock) as $row) {
            $sku = trim((string) $row['sku']);

            if ($sku === '') {
                $sku = $this->uniqueValue(
                    'product_variants',
                    'sku',
                    Str::upper(Str::slug($product->name, '-')).'-'.Str::upper(Str::slug((string) $row['size'] ?: 'OS', '')),
                );
            }

            $variant = $product->variants()->where('sku', $sku)->first();

            if ($variant === null && ProductVariant::query()->where('sku', $sku)->exists()) {
                $sku = $this->uniqueValue('product_variants', 'sku', $sku);
            }

            $barcode = trim((string) $row['barcode']);
            $barcode = $barcode === '' ? null : $barcode;

            if ($barcode !== null) {
                $barcode = $this->uniqueBarcode($barcode, $variant?->id);
            }

            if ($variant === null) {
                $variant = ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'color' => filled($row['color']) ? $row['color'] : null,
                    'size' => filled($row['size']) ? $row['size'] : null,
                    'price' => $row['price'] ?: $fallbackPrice,
                    'is_active' => true,
                ]);
            } else {
                $variant->update([
                    'barcode' => $barcode ?? $variant->barcode,
                    'color' => filled($row['color']) ? $row['color'] : $variant->color,
                    'size' => filled($row['size']) ? $row['size'] : $variant->size,
                    'price' => $row['price'] ?: $variant->price,
                ]);
            }

            $quantity = (int) $row['stock'];
            $stock = $variant->stock;

            if ($stock === null) {
                Stock::query()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'minimum_quantity' => $minStock,
                ]);
            } else {
                $stock->update([
                    'quantity' => $quantity,
                    'minimum_quantity' => $minStock,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{color: string, size: string, sku: string, barcode: string, stock: int, price: float}>
     */
    private function variantRows(array $data, float $fallbackPrice, int $fallbackStock): array
    {
        $variants = $data['variants'] ?? [];
        $rows = [];

        if (is_array($variants) && $variants !== [] && array_is_list($variants) && is_array($variants[0] ?? null)) {
            foreach ($variants as $variant) {
                $rows[] = [
                    'color' => (string) ($variant['color'] ?? ''),
                    'size' => (string) ($variant['size'] ?? ''),
                    'sku' => (string) ($variant['sku'] ?? ''),
                    'barcode' => (string) ($variant['barcode'] ?? ''),
                    'stock' => (int) ($variant['stock'] ?? $fallbackStock),
                    'price' => (float) ($variant['price'] ?? $fallbackPrice),
                ];
            }
        } elseif (is_array($variants)) {
            $colors = array_values((array) ($variants['color'] ?? []));
            $sizes = array_values((array) ($variants['size'] ?? []));
            $skus = array_values((array) ($variants['sku'] ?? []));
            $barcodes = array_values((array) ($variants['barcode'] ?? []));
            $stocks = array_values((array) ($variants['stock'] ?? []));
            $prices = array_values((array) ($variants['price'] ?? []));
            $count = max(count($colors), count($sizes), count($skus), count($barcodes), count($stocks), count($prices));

            for ($index = 0; $index < $count; $index++) {
                $rows[] = [
                    'color' => (string) ($colors[$index] ?? ''),
                    'size' => (string) ($sizes[$index] ?? ''),
                    'sku' => (string) ($skus[$index] ?? ''),
                    'barcode' => (string) ($barcodes[$index] ?? ''),
                    'stock' => (int) ($stocks[$index] ?? $fallbackStock),
                    'price' => (float) ($prices[$index] ?? $fallbackPrice),
                ];
            }
        }

        $rows = array_values(array_filter(
            $rows,
            fn (array $row): bool => $row['sku'] !== '' || $row['color'] !== '' || $row['size'] !== '' || $row['barcode'] !== '',
        ));

        if ($rows === []) {
            $rows[] = [
                'color' => '',
                'size' => '',
                'sku' => '',
                'barcode' => '',
                'stock' => $fallbackStock,
                'price' => $fallbackPrice,
            ];
        }

        return $rows;
    }

    private function uniqueBarcode(string $barcode, ?string $ignoreId = null): string
    {
        while (
            ProductVariant::query()
                ->where('barcode', $barcode)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $barcode .= '0';
        }

        return $barcode;
    }

    private function activeRegister(): ?CashRegister
    {
        return CashRegister::query()
            ->where('is_active', true)
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();
    }

    private function addCashTransaction(
        CashRegister $register,
        string $type,
        float $amount,
        ?string $description = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
    ): ?CashTransaction {
        if (! Schema::hasTable('cash_transactions')) {
            return null;
        }

        return CashTransaction::query()->create([
            'cash_register_id' => $register->id,
            'transaction_type' => $type,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);
    }

    /**
     * @param  array<string, mixed>  $checkout
     */
    private function customerFromCheckout(array $checkout): ?Customer
    {
        if (! Schema::hasTable('customers')) {
            return null;
        }

        $customer = Customer::query()->where('email', $checkout['email'])->first();

        if ($customer !== null) {
            return $customer;
        }

        $payload = [
            'first_name' => $checkout['first_name'],
            'last_name' => $checkout['last_name'],
            'email' => $checkout['email'],
            'is_active' => true,
        ];

        if ($this->hasSlug('customers')) {
            $payload['slug'] = $this->uniqueValue(
                'customers',
                'slug',
                Str::slug($checkout['first_name'].' '.$checkout['last_name']) ?: 'customer',
            );
        }

        return Customer::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $checkout
     */
    private function storeAddress(?Customer $customer, array $checkout): void
    {
        if ($customer === null || ! Schema::hasTable('customer_addresses')) {
            return;
        }

        CustomerAddress::query()->create([
            'customer_id' => $customer->id,
            'title' => 'Shipping',
            'first_name' => $checkout['first_name'],
            'last_name' => $checkout['last_name'],
            'city' => $checkout['city'],
            'district' => $checkout['city'],
            'address_line' => $checkout['address'],
            'postal_code' => $checkout['postal_code'],
            'is_default' => true,
        ]);
    }

    private function recordPayment(Order $order, string $method, float $amount): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => $method,
            'amount' => $amount,
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    private function storefrontVariant(int $catalogCode, string $size): ?ProductVariant
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'catalog_code')) {
            return null;
        }

        $product = Product::query()->where('catalog_code', $catalogCode)->with('variants')->first();

        if ($product === null) {
            return null;
        }

        $needle = Str::upper($size);

        return $product->variants->first(
            fn (ProductVariant $variant): bool => Str::upper((string) $variant->size) === $needle,
        );
    }

    private function customerBySlug(string $slug): ?Customer
    {
        if ($slug === '' || ! $this->hasSlug('customers')) {
            return null;
        }

        return Customer::query()->where('slug', $slug)->first();
    }

    private function moveStock(ProductVariant $variant, int $delta, string $type, ?string $referenceId = null): void
    {
        if (! Schema::hasTable('stocks')) {
            return;
        }

        $stock = $variant->stock;

        if ($stock === null) {
            $stock = Stock::query()->create([
                'product_variant_id' => $variant->id,
                'quantity' => max(0, $delta),
                'reserved_quantity' => 0,
                'minimum_quantity' => 5,
            ]);
        } else {
            $stock->update(['quantity' => max(0, (int) $stock->quantity + $delta)]);
        }

        if (Schema::hasTable('stock_movements')) {
            StockMovement::query()->create([
                'product_variant_id' => $variant->id,
                'movement_type' => $type,
                'quantity' => $delta,
                'reference_type' => $type,
                'reference_id' => $referenceId,
            ]);
        }
    }
}
