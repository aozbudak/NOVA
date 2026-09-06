<?php

namespace App\Support;

use App\Enums\StaffRole;
use App\Enums\StockMovementType;
use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\CashRegister;
use App\Models\CashTransaction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Exchange;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ReturnItem;
use App\Models\Role;
use App\Models\SaleReturn;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class DatabaseRecords
{
    /**
     * @param  array{id?: string, name: string, parent_id?: string|null, status?: string|null}  $record
     */
    public function saveCategory(array $record, ?string $id = null): ?Category
    {
        if (! Schema::hasTable('categories')) {
            return null;
        }

        $parentId = filled($record['parent_id'] ?? null) ? (string) $record['parent_id'] : null;
        $existing = $id !== null
            ? $this->findByUuidOrSlug(Category::class, $id)
            : null;

        $slugBase = Str::slug($record['name']) ?: 'category';

        if ($existing === null && $parentId !== null) {
            $parentSlug = $this->findByUuidOrSlug(Category::class, $parentId)?->slug;

            if (is_string($parentSlug) && $parentSlug !== '') {
                $slugBase = $parentSlug.'-'.$slugBase;
            }
        }

        $slug = $existing?->slug ?? $this->uniqueValue('categories', 'slug', $slugBase);

        $payload = [
            'parent_id' => $parentId,
            'name' => $record['name'],
            'slug' => $slug,
            'is_active' => ($record['status'] ?? 'active') !== 'inactive',
            'sort_order' => $existing?->sort_order ?? 0,
        ];

        if ($existing !== null) {
            $existing->update($payload);

            return $existing->fresh() ?? $existing;
        }

        return Category::query()->create($payload);
    }

    public function deleteCategory(string $id): bool
    {
        if (! Schema::hasTable('categories')) {
            return false;
        }

        $category = $this->findByUuidOrSlug(Category::class, $id);

        if ($category === null || $category->products()->exists()) {
            return false;
        }

        $category->delete();

        return true;
    }

    public function setCategoryHeader(string $id, bool $inHeader): ?Category
    {
        if (! Schema::hasTable('categories') || ! Schema::hasColumn('categories', 'show_in_header')) {
            return null;
        }

        $category = $this->findByUuidOrSlug(Category::class, $id);

        if ($category === null) {
            return null;
        }

        $category->update(['show_in_header' => $inHeader]);

        return $category->fresh() ?? $category;
    }

    /**
     * @param  array{name: string, slug?: string|null, description?: string|null, logo?: string|null, status?: string|null}  $record
     */
    public function saveBrand(array $record, ?string $id = null): ?Brand
    {
        if (! Schema::hasTable('brands')) {
            return null;
        }

        $existing = $id !== null
            ? $this->findByUuidOrSlug(Brand::class, $id)
            : null;

        $slug = filled($record['slug'] ?? null)
            ? Str::slug((string) $record['slug'])
            : Str::slug($record['name']);
        $slug = $slug !== '' ? $slug : 'brand';

        if ($existing === null || $existing->slug !== $slug) {
            $slug = $this->uniqueValue('brands', 'slug', $slug, $existing?->id);
        }

        $payload = [
            'name' => $record['name'],
            'slug' => $slug,
            'description' => filled($record['description'] ?? null) ? $record['description'] : null,
            'logo' => filled($record['logo'] ?? null) ? $record['logo'] : null,
            'is_active' => ($record['status'] ?? 'active') !== 'inactive',
        ];

        if ($existing !== null) {
            $existing->update($payload);

            return $existing->fresh() ?? $existing;
        }

        return Brand::query()->create($payload);
    }

    public function toggleBrand(string $id): ?Brand
    {
        $brand = $this->findByUuidOrSlug(Brand::class, $id);

        if ($brand === null) {
            return null;
        }

        $brand->update(['is_active' => ! $brand->is_active]);

        return $brand->fresh() ?? $brand;
    }

    public function deleteBrand(string $id): bool
    {
        $brand = $this->findByUuidOrSlug(Brand::class, $id);

        if ($brand === null) {
            return false;
        }

        $brand->delete();

        return true;
    }

    public function saveProduct(array $data, ?string $slug = null): ?Product
    {
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '' || ! Schema::hasTable('products')) {
            return null;
        }

        return DB::transaction(function () use ($data, $name, $slug): Product {
            $category = $this->resolveCategory($data['category'] ?? null);
            $brand = $this->resolveBrand($data['brand'] ?? null);
            $pricing = Price::breakdown($data['price'] ?? 0, $data['vat'] ?? 20);
            $price = (float) $pricing['gross'];
            $vatRate = (float) $pricing['rate'];
            $initialStock = max(0, (int) ($data['initial_stock'] ?? 0));
            $productSlug = $slug ?? $this->uniqueValue('products', 'slug', Str::slug($name) ?: 'product');
            $sku = trim((string) ($data['sku'] ?? ''));
            $department = $this->departmentSlug($category);
            $type = $this->typeSlug($category);

            $attributes = [
                'channel' => 'admin',
                'sku' => $sku !== '' ? $sku : Str::upper(Str::slug($name, '')),
                'purchase_price' => (float) Price::money($data['purchase_price'] ?? 0),
                'vat' => (int) $vatRate,
                'price_net' => $pricing['net'],
                'price_vat' => $pricing['vat'],
                'department' => $department,
                'type' => $type,
                'min_stock' => 0,
            ];

            $existing = $slug === null ? null : Product::query()->where('slug', $slug)->first();

            $values = [
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $productSlug,
                'description' => $data['description'] ?? null,
                'brand' => $brand?->name,
                'base_price' => $price,
                'sale_price' => null,
                'currency' => 'TRY',
                'is_active' => ($data['status'] ?? 'active') !== 'inactive',
                'catalog_code' => $existing?->catalog_code ?? $this->nextCatalogCode(),
                'attributes' => $attributes,
            ];

            if (Schema::hasColumn('products', 'brand_id')) {
                $values['brand_id'] = $brand?->id;
            }

            if (Schema::hasColumn('products', 'vat_rate')) {
                $values['vat_rate'] = $vatRate;
            }

            $oldPrice = $existing?->base_price;
            $product = $existing ?? Product::query()->create($values);

            if ($existing !== null) {
                $product->update($values);
            }

            $this->syncVariants($product, $data, $price, $initialStock, 0);
            $this->storeProductImages($product, $data);
            $this->recordAudit(
                $slug === null ? 'product.created' : 'product.updated',
                $product,
                $oldPrice === null ? null : ['base_price' => (float) $oldPrice],
                ['base_price' => $price, 'name' => $name],
            );

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
        $this->recordAudit('product.deactivated', $product, ['is_active' => true], ['is_active' => false]);

        return true;
    }

    /**
     * @param  array{id: string, name: string, email: string, phone?: string|null, password?: string|null}  $record
     */
    public function saveCustomer(array $record): ?Customer
    {
        if (! $this->hasSlug('customers')) {
            return null;
        }

        [$firstName, $lastName] = $this->splitName($record['name']);

        return DB::transaction(function () use ($record, $firstName, $lastName): Customer {
            $user = null;

            if (filled($record['password'] ?? null) && Schema::hasTable('users')) {
                $userPayload = [
                    'name' => $record['name'],
                    'email' => $record['email'],
                    'password' => $record['password'],
                    'phone' => filled($record['phone'] ?? null) ? $record['phone'] : null,
                    'is_active' => true,
                ];

                if (Schema::hasColumn('users', 'slug')) {
                    $userPayload['slug'] = $this->uniqueValue('users', 'slug', $record['id']);
                }

                $user = User::query()->updateOrCreate(
                    ['email' => $record['email']],
                    $userPayload,
                );
            }

            return Customer::query()->updateOrCreate(
                ['slug' => $record['id']],
                [
                    'user_id' => $user?->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $record['email'],
                    'phone' => filled($record['phone'] ?? null) ? $record['phone'] : null,
                    'is_active' => true,
                ],
            );
        });
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

        if (Schema::hasColumn('users', 'username') && filled($record['username'] ?? null)) {
            $payload['username'] = $record['username'];
        }

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

    /**
     * @param  array{id: string, name: string, contact?: string|null, email?: string|null, phone?: string|null, address?: string|null, tax?: string|null, status?: string|null}|null  $catalog
     */
    public function resolveSupplierUuid(?string $key, ?array $catalog = null): ?string
    {
        if (! filled($key) || ! Schema::hasTable('suppliers')) {
            return null;
        }

        $existing = $this->findByUuidOrSlug(Supplier::class, $key);

        if ($existing !== null) {
            return $existing->id;
        }

        if ($catalog === null) {
            return null;
        }

        return $this->saveSupplier($catalog)?->id;
    }

    public function adjustStock(string $sku, int $quantity, ?string $reason = null, string $type = 'adjustment', ?string $supplierId = null): bool
    {
        if (! Schema::hasTable('product_variants') || ! Schema::hasTable('stocks')) {
            return false;
        }

        $variant = ProductVariant::query()->where('sku', $sku)->first();

        if ($variant === null) {
            return false;
        }

        $delta = $quantity;
        $movementType = $type;
        $resolvedSupplierId = $supplierId;

        if (in_array($type, ['in', 'purchase'], true)) {
            $delta = abs($quantity);
            $movementType = StockMovementType::Purchase->value;
        } elseif ($type === 'sale') {
            $delta = -abs($quantity);
            $movementType = StockMovementType::Sale->value;
            $resolvedSupplierId = null;
        } elseif ($type === 'out') {
            $delta = -abs($quantity);
            $movementType = StockMovementType::AdjustmentOut->value;
            $resolvedSupplierId = null;
        }

        return DB::transaction(function () use ($variant, $delta, $movementType, $reason, $resolvedSupplierId): true {
            if ($delta > 0) {
                $this->ensureSystemBarcode($variant);
            }

            $this->moveStock($variant, $delta, $movementType, null, 'adjustment', $reason, $resolvedSupplierId);

            return true;
        });
    }

    public function openRegister(float $opening = 0): ?CashRegister
    {
        if (! Schema::hasTable('cash_registers')) {
            return null;
        }

        return DB::transaction(function () use ($opening): CashRegister {
            $existing = CashRegister::query()
                ->where('is_active', true)
                ->whereNull('closed_at')
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                throw ValidationException::withMessages([
                    'opening' => __('admin.cash.already_open'),
                ]);
            }

            $register = CashRegister::query()->create([
                'name' => 'Main',
                'opening_balance' => $opening,
                'is_active' => true,
                'opened_at' => now(),
                'closed_at' => null,
            ]);

            $this->addCashTransaction($register, 'opening', $opening, 'Opening balance');
            $this->recordAudit('cash.opened', $register, null, ['opening_balance' => $opening]);

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
            throw ValidationException::withMessages([
                'actual' => __('admin.cash.not_open'),
            ]);
        }

        return DB::transaction(function () use ($register, $actual): CashRegister {
            $locked = CashRegister::query()->whereKey($register->id)->lockForUpdate()->first();

            if ($locked === null || ! $locked->is_active) {
                throw ValidationException::withMessages([
                    'actual' => __('admin.cash.not_open'),
                ]);
            }

            $closingAmount = $actual ?? (float) $locked->opening_balance;

            $locked->update([
                'is_active' => false,
                'closed_at' => now(),
            ]);

            $this->addCashTransaction($locked, 'closing', $closingAmount, 'Closing balance');
            $this->recordAudit('cash.closed', $locked, ['is_active' => true], [
                'is_active' => false,
                'actual' => $closingAmount,
            ]);

            return $locked;
        });
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
            return false;
        }

        if (! Hash::check($password, $user->password)) {
            return false;
        }

        if (! Schema::hasTable('customers')) {
            return false;
        }

        $customer = Customer::query()->where('user_id', $user->id)->first()
            ?? Customer::query()->where('email', $email)->first();

        if ($customer === null) {
            return false;
        }

        $customer->setRelation('user', $user);

        return $customer;
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
            $orderNumber = $this->uniqueOrderNumber($orderNumber);
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
                $variant = $this->storefrontVariant(
                    (int) $line['product']['id'],
                    (string) $line['size'],
                    isset($line['color']) ? (string) $line['color'] : null,
                );
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
            $this->recordAudit('order.placed', $order, null, [
                'order_number' => $order->order_number,
                'total_amount' => $subtotal,
            ]);

            return $order;
        });
    }

    /**
     * @param  array{payment: string, customer_id?: string|null, note?: string|null, items: list<array{sku: string, quantity: int, discount?: float|int|string|null}>}  $sale
     * @param  Collection<int, array<string, mixed>>  $lines
     */
    public function placeSale(array $sale, Collection $lines, string $number): ?Order
    {
        if (! Schema::hasTable('orders')) {
            return null;
        }

        return DB::transaction(function () use ($sale, $lines, $number): Order {
            $resolved = $this->resolveSaleLines($sale['items'] ?? $lines->all());
            $customer = $this->customerBySlug((string) ($sale['customer_id'] ?? ''));
            $subtotal = (float) $resolved->sum(fn (array $line): float => $line['unit'] * $line['qty']);
            $discount = (float) $resolved->sum('discount');
            $total = (float) $resolved->sum('total');
            $number = $this->nextPosOrderNumber($number);

            $order = Order::query()->create([
                'order_number' => $number,
                'customer_id' => $customer?->id,
                'status' => 'completed',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'currency' => 'TRY',
                'notes' => 'pos:'.$this->cashierName(),
            ]);

            foreach ($resolved as $line) {
                /** @var ProductVariant $variant */
                $variant = $line['variant_model'];

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $line['product'],
                    'sku' => $line['sku'],
                    'color' => $line['color'],
                    'size' => $line['size'],
                    'quantity' => (int) $line['qty'],
                    'unit_price' => $line['unit'],
                    'discount_amount' => $line['discount'],
                    'total_price' => $line['total'],
                ]);

                $this->moveStock(
                    $variant,
                    -((int) $line['qty']),
                    StockMovementType::Sale->value,
                    $order->id,
                    'sale',
                    $order->order_number,
                );
            }

            $this->recordPayment($order, $sale['payment'], $total, $sale['note'] ?? null);

            $register = $this->activeRegister();

            if ($register !== null && $sale['payment'] === 'cash') {
                $this->addCashTransaction($register, 'sale', $total, $number, 'order', $order->id);
            }

            $this->recordAudit('sale.completed', $order, null, [
                'order_number' => $order->order_number,
                'total_amount' => $total,
                'payment' => $sale['payment'],
            ]);

            return $order;
        });
    }

    public function completeReturn(string $saleNumber, ?string $reason = null, ?string $notes = null): ?SaleReturn
    {
        if (! Schema::hasTable('returns') || ! Schema::hasTable('orders')) {
            return null;
        }

        return DB::transaction(function () use ($saleNumber, $reason, $notes): ?SaleReturn {
            $order = Order::query()
                ->where('order_number', $saleNumber)
                ->with('items.variant')
                ->lockForUpdate()
                ->first();

            if ($order === null) {
                return null;
            }

            $alreadyReturned = $order->status === 'returned'
                || SaleReturn::query()->where('order_id', $order->id)->lockForUpdate()->exists();

            if ($alreadyReturned) {
                throw ValidationException::withMessages([
                    'sale' => __('admin.returns.already_returned'),
                ]);
            }

            $refundAmount = (float) $order->total_amount;
            $originalPayment = Schema::hasTable('payments')
                ? Payment::query()
                    ->where('order_id', $order->id)
                    ->where('status', 'completed')
                    ->orderBy('id')
                    ->first()
                : null;

            $return = SaleReturn::query()->create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'return_number' => $this->nextReturnNumber(),
                'reason' => $reason,
                'status' => 'completed',
                'total_amount' => $refundAmount,
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
                    'reason' => ($reason === 'other' && filled($notes)) ? $notes : $reason,
                ]);

                if ($item->variant !== null) {
                    $this->moveStock(
                        $item->variant,
                        (int) $item->quantity,
                        StockMovementType::Return->value,
                        $return->id,
                        'return',
                        $order->order_number,
                    );
                }
            }

            $previousStatus = $order->status;

            $order->update(['status' => 'returned']);

            if (Schema::hasTable('payments')) {
                Payment::query()->create([
                    'order_id' => $order->id,
                    'payment_method' => $originalPayment?->payment_method ?? 'cash',
                    'amount' => $refundAmount,
                    'status' => 'refunded',
                    'transaction_reference' => 'refund:'.$return->return_number,
                    'paid_at' => now(),
                ]);
            }

            $register = $this->activeRegister();

            if ($register !== null && ($originalPayment?->payment_method ?? null) === 'cash') {
                $this->addCashTransaction(
                    $register,
                    'refund',
                    -abs($refundAmount),
                    $return->return_number.' · '.$order->order_number,
                    'return',
                    $return->id,
                );
            }

            $this->recordAudit('return.completed', $return, ['status' => $previousStatus], [
                'return_number' => $return->return_number,
                'order_number' => $order->order_number,
                'total_amount' => $refundAmount,
            ]);

            return $return;
        });
    }

    public function completeExchange(string $saleNumber, string $originalSku, string $newSku, int $quantity): ?Exchange
    {
        if (! Schema::hasTable('exchanges') || ! Schema::hasTable('orders') || ! Schema::hasTable('product_variants')) {
            return null;
        }

        return DB::transaction(function () use ($saleNumber, $originalSku, $newSku, $quantity): ?Exchange {
            $order = Order::query()
                ->where('order_number', $saleNumber)
                ->with(['items.variant.product', 'payments'])
                ->lockForUpdate()
                ->first();

            if ($order === null) {
                return null;
            }

            if (in_array($order->status, ['returned', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'sale' => __('admin.exchanges.already_returned'),
                ]);
            }

            $fullReturnExists = SaleReturn::query()
                ->where('order_id', $order->id)
                ->where(function ($query): void {
                    $query->whereNull('reason')->orWhere('reason', '!=', 'exchange');
                })
                ->lockForUpdate()
                ->exists();

            if ($fullReturnExists) {
                throw ValidationException::withMessages([
                    'sale' => __('admin.exchanges.already_returned'),
                ]);
            }

            $orderItem = $order->items->first(
                fn (OrderItem $item): bool => Str::lower((string) $item->sku) === Str::lower($originalSku),
            );

            if ($orderItem === null || $orderItem->variant === null) {
                throw ValidationException::withMessages([
                    'original_sku' => __('admin.exchanges.item_not_on_sale'),
                ]);
            }

            $returnedQty = (int) ReturnItem::query()->where('order_item_id', $orderItem->id)->sum('quantity');
            $remaining = (int) $orderItem->quantity - $returnedQty;

            if ($quantity > $remaining) {
                throw ValidationException::withMessages([
                    'quantity' => __('admin.exchanges.insufficient_quantity'),
                ]);
            }

            $newVariant = ProductVariant::query()
                ->where('sku', $newSku)
                ->with(['product', 'stock'])
                ->lockForUpdate()
                ->first();

            if ($newVariant === null) {
                throw ValidationException::withMessages([
                    'new_sku' => __('admin.exchanges.new_not_found'),
                ]);
            }

            if ($newVariant->id === $orderItem->product_variant_id) {
                throw ValidationException::withMessages([
                    'new_sku' => __('admin.exchanges.same_item'),
                ]);
            }

            $oldVariant = $orderItem->variant;
            $oldPrice = (float) $orderItem->unit_price;
            $newPrice = (float) $newVariant->price;
            $difference = round(($newPrice - $oldPrice) * $quantity, 2);
            $lineTotal = round($oldPrice * $quantity, 2);

            $return = SaleReturn::query()->create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'return_number' => $this->nextExchangeNumber(),
                'reason' => 'exchange',
                'status' => 'completed',
                'total_amount' => $lineTotal,
                'approved_at' => now(),
                'completed_at' => now(),
            ]);

            ReturnItem::query()->create([
                'return_id' => $return->id,
                'order_item_id' => $orderItem->id,
                'quantity' => $quantity,
                'unit_price' => $oldPrice,
                'total_price' => $lineTotal,
                'reason' => 'exchange',
            ]);

            $exchange = Exchange::query()->create([
                'return_id' => $return->id,
                'old_product_variant_id' => $oldVariant->id,
                'new_product_variant_id' => $newVariant->id,
                'quantity' => $quantity,
                'price_difference' => $difference,
                'status' => 'completed',
            ]);

            $this->moveStock(
                $oldVariant,
                $quantity,
                StockMovementType::ExchangeIn->value,
                $exchange->id,
                'exchange',
                $return->return_number,
            );

            $this->moveStock(
                $newVariant,
                -$quantity,
                StockMovementType::ExchangeOut->value,
                $exchange->id,
                'exchange',
                $return->return_number,
            );

            $originalPayment = $order->payments
                ->firstWhere('status', 'completed')
                ?? $order->payments->first();

            if ($difference !== 0.0 && Schema::hasTable('payments')) {
                Payment::query()->create([
                    'order_id' => $order->id,
                    'payment_method' => $originalPayment?->payment_method ?? 'cash',
                    'amount' => abs($difference),
                    'status' => $difference > 0 ? 'completed' : 'refunded',
                    'transaction_reference' => ($difference > 0 ? 'exchange:' : 'refund:').$return->return_number,
                    'paid_at' => now(),
                ]);
            }

            $register = $this->activeRegister();

            if ($register !== null && ($originalPayment?->payment_method ?? null) === 'cash' && $difference !== 0.0) {
                $this->addCashTransaction(
                    $register,
                    $difference > 0 ? 'sale' : 'refund',
                    $difference > 0 ? $difference : -abs($difference),
                    $return->return_number.' · '.$order->order_number,
                    'exchange',
                    $exchange->id,
                );
            }

            $this->recordAudit('exchange.completed', $exchange, null, [
                'return_number' => $return->return_number,
                'order_number' => $order->order_number,
                'old_sku' => $oldVariant->sku,
                'new_sku' => $newVariant->sku,
                'price_difference' => $difference,
            ]);

            return $exchange;
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

    /**
     * @param  array{first_name: string, last_name: string, email: string, phone?: string|null}  $data
     */
    public function updateCustomerProfile(string $email, array $data): ?Customer
    {
        if (! Schema::hasTable('customers')) {
            return null;
        }

        $customer = Customer::query()->where('email', $email)->first();

        if ($customer === null) {
            return null;
        }

        return DB::transaction(function () use ($customer, $email, $data): Customer {
            $phone = filled($data['phone'] ?? null) ? $data['phone'] : null;

            $customer->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $phone,
            ]);

            $user = $customer->user ?? User::query()->where('email', $email)->first();

            $user?->update([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'phone' => $phone,
            ]);

            return $customer->fresh() ?? $customer;
        });
    }

    public function updateCustomerPassword(string $email, string $current, string $password): bool
    {
        if (! Schema::hasTable('users')) {
            return false;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return false;
        }

        if (! Hash::check($current, $user->password)) {
            return false;
        }

        $user->update(['password' => $password]);

        return true;
    }

    /**
     * @param  array{title: string, first_name: string, last_name: string, phone?: string|null, city: string, district: string, address_line: string, postal_code?: string|null, is_default?: bool}  $data
     */
    public function saveCustomerAddress(Customer $customer, array $data, ?CustomerAddress $address = null): ?CustomerAddress
    {
        if (! Schema::hasTable('customer_addresses')) {
            return null;
        }

        return DB::transaction(function () use ($customer, $data, $address): CustomerAddress {
            $makeDefault = (bool) ($data['is_default'] ?? false) || $customer->addresses()->doesntExist();

            if ($makeDefault) {
                $customer->addresses()->update(['is_default' => false]);
            }

            $payload = [
                'title' => $data['title'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => filled($data['phone'] ?? null) ? $data['phone'] : null,
                'city' => $data['city'],
                'district' => $data['district'],
                'address_line' => $data['address_line'],
                'postal_code' => filled($data['postal_code'] ?? null) ? $data['postal_code'] : null,
                'is_default' => $makeDefault,
            ];

            if ($address instanceof CustomerAddress) {
                $address->update($payload);

                return $address->fresh() ?? $address;
            }

            return $customer->addresses()->create($payload);
        });
    }

    public function deleteCustomerAddress(Customer $customer, CustomerAddress $address): void
    {
        if (! Schema::hasTable('customer_addresses')) {
            return;
        }

        DB::transaction(function () use ($customer, $address): void {
            $wasDefault = $address->is_default;
            $address->delete();

            if (! $wasDefault) {
                return;
            }

            $customer->addresses()
                ->orderBy('created_at')
                ->orderBy('id')
                ->first()
                ?->update(['is_default' => true]);
        });
    }

    public function setDefaultCustomerAddress(Customer $customer, CustomerAddress $address): void
    {
        if (! Schema::hasTable('customer_addresses')) {
            return;
        }

        DB::transaction(function () use ($customer, $address): void {
            $customer->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });
    }

    public function updatePassword(string $email, string $current, string $password): bool
    {
        if (! Schema::hasTable('users')) {
            return false;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return false;
        }

        if (! Hash::check($current, $user->password)) {
            return false;
        }

        $user->update(['password' => $password]);

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function storeProductImages(Product $product, array $data): void
    {
        $files = $this->uploadedImages($data);

        if ($files === [] || ! Schema::hasTable('product_images')) {
            return;
        }

        $existing = $product->images()->count();
        $files = array_slice($files, 0, max(0, 4 - $existing));

        foreach ($files as $index => $file) {
            $path = $file->store('products/'.$product->id, 'public');

            if (! is_string($path) || $path === '') {
                continue;
            }

            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_url' => Storage::disk('public')->url($path),
                'alt_text' => $product->name,
                'sort_order' => $existing + $index,
                'is_primary' => $existing === 0 && $index === 0,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<UploadedFile>
     */
    private function uploadedImages(array $data): array
    {
        $images = $data['images'] ?? [];

        if ($images instanceof UploadedFile) {
            $images = [$images];
        }

        if (! is_array($images)) {
            return [];
        }

        return array_values(array_filter(
            $images,
            fn (mixed $file): bool => $file instanceof UploadedFile && $file->isValid(),
        ));
    }

    private function hasSlug(string $table): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, 'slug');
    }

    private function uniqueValue(string $table, string $column, string $base, ?string $ignoreId = null): string
    {
        $value = $base === '' ? 'item' : $base;
        $original = $value;
        $suffix = 2;

        while (DB::table($table)
            ->where($column, $value)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $value = $original.'-'.$suffix;
            $suffix++;
        }

        return $value;
    }

    private function nextCatalogCode(): int
    {
        $max = (int) Product::query()->max('catalog_code');

        return max($max, 1000) + 1;
    }

    private function ensureSystemBarcode(ProductVariant $variant): void
    {
        if (filled($variant->barcode)) {
            return;
        }

        $variant->update(['barcode' => $this->nextSystemBarcode($variant->id)]);
    }

    private function nextSystemBarcode(?string $ignoreId = null): string
    {
        $sequence = $this->nextSystemBarcodeSequence();

        do {
            $body = '200'.str_pad((string) $sequence, 9, '0', STR_PAD_LEFT);
            $barcode = $body.$this->ean13CheckDigit($body);
            $sequence++;
        } while ($this->barcodeTaken($barcode, $ignoreId));

        return $barcode;
    }

    private function nextSystemBarcodeSequence(): int
    {
        $max = 0;

        ProductVariant::query()
            ->where('barcode', 'like', '200%')
            ->select('barcode')
            ->cursor()
            ->each(function (ProductVariant $variant) use (&$max): void {
                if (preg_match('/^200(\d{9})\d$/', (string) $variant->barcode, $matches) === 1) {
                    $max = max($max, (int) $matches[1]);
                }
            });

        return $max + 1;
    }

    private function ean13CheckDigit(string $twelveDigits): string
    {
        $sum = 0;

        for ($index = 0; $index < 12; $index++) {
            $digit = (int) $twelveDigits[$index];
            $sum += ($index % 2 === 0) ? $digit : $digit * 3;
        }

        return (string) ((10 - ($sum % 10)) % 10);
    }

    private function resolveCategory(mixed $value): Category
    {
        $value = is_string($value) || is_int($value) ? trim((string) $value) : '';

        if ($value !== '') {
            $category = Category::query()
                ->where(function ($query) use ($value): void {
                    $query->where('slug', $value)->orWhere('name', $value);

                    if (Str::isUuid($value)) {
                        $query->orWhere('id', $value);
                    }
                })
                ->first();

            if ($category !== null) {
                return $category;
            }
        }

        $fallback = Category::query()->orderBy('sort_order')->orderBy('name')->first();

        if ($fallback !== null) {
            return $fallback;
        }

        return Category::query()->create([
            'name' => $value !== '' ? $value : 'General',
            'slug' => $this->uniqueValue('categories', 'slug', Str::slug($value !== '' ? $value : 'general') ?: 'general'),
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function resolveBrand(mixed $value): ?Brand
    {
        if (! Schema::hasTable('brands')) {
            return null;
        }

        $value = is_string($value) || is_int($value) ? trim((string) $value) : '';

        if ($value === '') {
            return null;
        }

        $existing = Brand::query()
            ->where(function ($query) use ($value): void {
                $query->where('slug', $value)->orWhere('name', $value);

                if (Str::isUuid($value)) {
                    $query->orWhere('id', $value);
                }
            })
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $slug = Str::slug($value) ?: 'brand';

        return Brand::query()->create([
            'name' => $value,
            'slug' => $this->uniqueValue('brands', 'slug', $slug),
            'is_active' => true,
        ]);
    }

    private function departmentSlug(Category $category): string
    {
        $category->loadMissing('parent');
        $root = $category->parent ?? $category;
        $slug = Str::lower($root->slug);
        $name = Str::lower($root->name);

        return match (true) {
            str_contains($slug, 'women') || str_contains($name, 'kadın') || str_contains($name, 'kadin') || str_contains($name, 'women') => 'women',
            str_contains($slug, 'men') || str_contains($name, 'erkek') || str_contains($name, 'men') => 'men',
            str_contains($slug, 'kid') || str_contains($name, 'çocuk') || str_contains($name, 'cocuk') || str_contains($name, 'kids') => 'kids',
            str_contains($slug, 'sport') || str_contains($name, 'spor') => 'sport',
            default => 'women',
        };
    }

    private function typeSlug(Category $category): string
    {
        $slug = $category->slug;

        foreach (['women-', 'men-', 'kids-', 'sport-', 'admin-'] as $prefix) {
            if (str_starts_with($slug, $prefix)) {
                return substr($slug, strlen($prefix));
            }
        }

        return $slug;
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
            $providedSku = $sku !== '';

            if ($sku === '') {
                $sku = $this->uniqueValue(
                    'product_variants',
                    'sku',
                    Str::upper(Str::slug($product->name, '-')).'-'.Str::upper(Str::slug((string) $row['size'] ?: 'OS', '')),
                );
            }

            $variant = $product->variants()->where('sku', $sku)->first();

            if ($variant === null && ProductVariant::query()->where('sku', $sku)->exists()) {
                if ($providedSku) {
                    throw ValidationException::withMessages([
                        'sku' => __('validation.unique', ['attribute' => 'sku']),
                    ]);
                }

                $sku = $this->uniqueValue('product_variants', 'sku', $sku);
            }

            $barcode = trim((string) $row['barcode']);
            $barcode = $barcode === '' ? null : $barcode;

            if ($barcode !== null && $this->barcodeTaken($barcode, $variant?->id)) {
                throw ValidationException::withMessages([
                    'barcode' => __('validation.unique', ['attribute' => 'barcode']),
                ]);
            }

            if ($barcode === null && ($variant === null || ! filled($variant->barcode))) {
                $barcode = $this->nextSystemBarcode($variant?->id);
            }

            $active = (bool) ($row['is_active'] ?? true);

            if ($variant === null) {
                $variant = ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'barcode' => $barcode,
                    'color' => filled($row['color']) ? $row['color'] : null,
                    'size' => filled($row['size']) ? $row['size'] : null,
                    'price' => $row['price'] ?: $fallbackPrice,
                    'is_active' => $active,
                ]);
            } else {
                $variant->update([
                    'barcode' => $barcode ?? $variant->barcode,
                    'color' => filled($row['color']) ? $row['color'] : $variant->color,
                    'size' => filled($row['size']) ? $row['size'] : $variant->size,
                    'price' => $row['price'] ?: $variant->price,
                    'is_active' => $active,
                ]);
            }

            $quantity = max(0, (int) $row['stock']);
            $stock = $variant->stock;

            if ($stock === null) {
                Stock::query()->create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'minimum_quantity' => $minStock,
                ]);

                if ($quantity !== 0) {
                    $this->writeStockMovement(
                        $variant,
                        $quantity,
                        StockMovementType::AdjustmentIn->value,
                        'adjustment',
                        null,
                        'Initial stock',
                    );
                }
            } else {
                if ((int) $stock->minimum_quantity !== $minStock) {
                    $stock->update(['minimum_quantity' => $minStock]);
                }

                $delta = $quantity - (int) $stock->quantity;

                if ($delta !== 0) {
                    $this->moveStock(
                        $variant,
                        $delta,
                        $delta > 0
                            ? StockMovementType::AdjustmentIn->value
                            : StockMovementType::AdjustmentOut->value,
                        null,
                        'adjustment',
                        'Variant stock sync',
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{color: string, size: string, sku: string, barcode: string, stock: int, price: float, is_active: bool}>
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
                    'is_active' => filter_var($variant['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            }
        } elseif (is_array($variants)) {
            $colors = array_values((array) ($variants['color'] ?? []));
            $sizes = array_values((array) ($variants['size'] ?? []));
            $skus = array_values((array) ($variants['sku'] ?? []));
            $barcodes = array_values((array) ($variants['barcode'] ?? []));
            $stocks = array_values((array) ($variants['stock'] ?? []));
            $prices = array_values((array) ($variants['price'] ?? []));
            $actives = array_values((array) ($variants['is_active'] ?? []));
            $count = max(count($colors), count($sizes), count($skus), count($barcodes), count($stocks), count($prices), count($actives));

            for ($index = 0; $index < $count; $index++) {
                $rows[] = [
                    'color' => (string) ($colors[$index] ?? ''),
                    'size' => (string) ($sizes[$index] ?? ''),
                    'sku' => (string) ($skus[$index] ?? ''),
                    'barcode' => (string) ($barcodes[$index] ?? ''),
                    'stock' => (int) ($stocks[$index] ?? $fallbackStock),
                    'price' => (float) ($prices[$index] ?? $fallbackPrice),
                    'is_active' => filter_var($actives[$index] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];
            }
        }

        $seenSku = [];
        $seenBarcode = [];

        foreach ($rows as $index => $row) {
            $sku = Str::upper(trim($row['sku']));
            $barcode = trim($row['barcode']);

            if ($sku !== '' && isset($seenSku[$sku])) {
                throw ValidationException::withMessages([
                    'variants.sku.'.$index => __('validation.unique', ['attribute' => 'sku']),
                ]);
            }

            if ($barcode !== '' && isset($seenBarcode[$barcode])) {
                throw ValidationException::withMessages([
                    'variants.barcode.'.$index => __('validation.unique', ['attribute' => 'barcode']),
                ]);
            }

            if ($sku !== '') {
                $seenSku[$sku] = true;
            }

            if ($barcode !== '') {
                $seenBarcode[$barcode] = true;
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
                'is_active' => true,
            ];
        }

        return $rows;
    }

    private function barcodeTaken(string $barcode, ?string $ignoreId = null): bool
    {
        return ProductVariant::query()
            ->where('barcode', $barcode)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
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

        if ($customer->addresses()->where('is_default', true)->exists()) {
            $customer->addresses()->update(['is_default' => false]);
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

    private function recordPayment(Order $order, string $method, float $amount, ?string $note = null): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => $method,
            'amount' => $amount,
            'status' => 'completed',
            'transaction_reference' => filled($note) ? $note : null,
            'paid_at' => now(),
        ]);
    }

    private function storefrontVariant(int $catalogCode, string $size, ?string $color = null): ?ProductVariant
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'catalog_code')) {
            return null;
        }

        $product = Product::query()->where('catalog_code', $catalogCode)->with('variants')->first();

        if ($product === null) {
            return null;
        }

        $needle = Str::upper($size);
        $colorNeedle = $color === null || $color === '' ? null : Str::upper($color);

        $match = $product->variants
            ->filter(fn (ProductVariant $variant): bool => $variant->is_active)
            ->first(function (ProductVariant $variant) use ($needle, $colorNeedle): bool {
                if (Str::upper((string) $variant->size) !== $needle) {
                    return false;
                }

                if ($colorNeedle === null) {
                    return true;
                }

                return Str::upper((string) $variant->color) === $colorNeedle;
            });

        return $match ?? $product->variants->first(
            fn (ProductVariant $variant): bool => Str::upper((string) $variant->size) === $needle,
        );
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @return TModel|null
     */
    private function findByUuidOrSlug(string $model, string $key): ?Model
    {
        return $model::query()
            ->where(function ($query) use ($key): void {
                $query->where('slug', $key);

                if (Str::isUuid($key)) {
                    $query->orWhere('id', $key);
                }
            })
            ->first();
    }

    private function customerBySlug(string $slug): ?Customer
    {
        if ($slug === '' || ! $this->hasSlug('customers')) {
            return null;
        }

        return Customer::query()->where('slug', $slug)->first();
    }

    private function moveStock(ProductVariant $variant, int $delta, string $type, ?string $referenceId = null, ?string $referenceType = null, ?string $note = null, ?string $supplierId = null): void
    {
        if (! Schema::hasTable('stocks') || $delta === 0) {
            return;
        }

        $stock = Stock::query()
            ->where('product_variant_id', $variant->id)
            ->lockForUpdate()
            ->first();

        if ($stock === null) {
            if ($delta < 0 && ! $this->allowsNegativeStock()) {
                throw ValidationException::withMessages([
                    'quantity' => __('admin.inventory.insufficient'),
                ]);
            }

            $stock = Stock::query()->create([
                'product_variant_id' => $variant->id,
                'quantity' => $delta,
                'reserved_quantity' => 0,
                'minimum_quantity' => 5,
            ]);
        } else {
            $available = (int) $stock->quantity - (int) $stock->reserved_quantity;
            $next = (int) $stock->quantity + $delta;

            if ($delta < 0 && $available + $delta < 0 && ! $this->allowsNegativeStock()) {
                throw ValidationException::withMessages([
                    'quantity' => __('admin.inventory.insufficient'),
                ]);
            }

            $stock->update(['quantity' => $this->allowsNegativeStock() ? $next : max(0, $next)]);
        }

        $this->writeStockMovement($variant, $delta, $type, $referenceType ?? $type, $referenceId, $note, $supplierId);
    }

    private function writeStockMovement(
        ProductVariant $variant,
        int $quantity,
        string $type,
        string $referenceType,
        ?string $referenceId = null,
        ?string $note = null,
        ?string $supplierId = null,
    ): void {
        if (! Schema::hasTable('stock_movements')) {
            return;
        }

        $movement = StockMovementType::fromIntent($type, $quantity);
        $amount = abs($quantity);

        if ($amount === 0) {
            return;
        }

        $payload = [
            'product_variant_id' => $variant->id,
            'user_id' => $this->actorUserId(),
            'movement_type' => $movement->value,
            'quantity' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'note' => $note,
        ];

        if (Schema::hasColumn('stock_movements', 'supplier_id')) {
            $payload['supplier_id'] = $supplierId;
        }

        StockMovement::query()->create($payload);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function resolveSaleLines(array $items): Collection
    {
        $merged = [];

        foreach ($items as $item) {
            $sku = (string) ($item['sku'] ?? '');

            if ($sku === '') {
                continue;
            }

            if (! isset($merged[$sku])) {
                $merged[$sku] = [
                    'sku' => $sku,
                    'quantity' => 0,
                    'discount' => 0.0,
                ];
            }

            $merged[$sku]['quantity'] += (int) ($item['quantity'] ?? $item['qty'] ?? 0);
            $merged[$sku]['discount'] += (float) ($item['discount'] ?? 0);
        }

        $skus = collect($merged)->keys()->sort()->values();

        $variants = ProductVariant::query()
            ->whereIn('sku', $skus->all())
            ->with('product')
            ->lockForUpdate()
            ->get()
            ->keyBy('sku');

        Stock::query()
            ->whereIn('product_variant_id', $variants->pluck('id')->all())
            ->lockForUpdate()
            ->get();

        return collect($merged)->values()->map(function (array $item) use ($variants): array {
            $variant = $variants->get($item['sku']);

            if ($variant === null) {
                throw ValidationException::withMessages([
                    'items' => __('validation.exists', ['attribute' => 'sku']),
                ]);
            }

            $quantity = (int) $item['quantity'];
            $discount = round((float) $item['discount'], 2);
            $unit = (float) ($variant->price ?? $variant->product?->base_price ?? 0);
            $gross = round($unit * $quantity, 2);

            if ($discount > $gross) {
                throw ValidationException::withMessages([
                    'items' => __('admin.pos.discount_exceeds'),
                ]);
            }

            $stock = Stock::query()
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();
            $available = $stock === null
                ? 0
                : (int) $stock->quantity - (int) $stock->reserved_quantity;

            if ($quantity > $available && ! $this->allowsNegativeStock()) {
                throw ValidationException::withMessages([
                    'items' => __('admin.pos.insufficient_stock'),
                ]);
            }

            $color = $variant->color;
            $size = $variant->size;

            return [
                'variant_model' => $variant,
                'product' => $variant->product?->name ?? $item['sku'],
                'variant' => trim(($color ?? '').' / '.($size ?? ''), ' /'),
                'color' => $color,
                'size' => $size,
                'sku' => $variant->sku,
                'qty' => $quantity,
                'unit' => $unit,
                'discount' => $discount,
                'total' => round($gross - $discount, 2),
            ];
        });
    }

    private function nextPosOrderNumber(string $fallback): string
    {
        $prefix = 'NV-'.now()->format('Ymd').'-';
        $latest = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->lockForUpdate()
            ->value('order_number');

        $sequence = 1;

        if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches) === 1) {
            $sequence = (int) $matches[1] + 1;
        }

        $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        return $this->uniqueOrderNumber($number !== '' ? $number : $fallback);
    }

    private function nextReturnNumber(): string
    {
        $prefix = 'RT-'.now()->format('Ymd').'-';
        $latest = SaleReturn::query()
            ->where('return_number', 'like', $prefix.'%')
            ->orderByDesc('return_number')
            ->lockForUpdate()
            ->value('return_number');

        $sequence = 1;

        if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches) === 1) {
            $sequence = (int) $matches[1] + 1;
        }

        $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        while (SaleReturn::query()->where('return_number', $number)->exists()) {
            $sequence++;
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        }

        return $number;
    }

    private function nextExchangeNumber(): string
    {
        $prefix = 'EX-'.now()->format('Ymd').'-';
        $latest = SaleReturn::query()
            ->where('return_number', 'like', $prefix.'%')
            ->orderByDesc('return_number')
            ->lockForUpdate()
            ->value('return_number');

        $sequence = 1;

        if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches) === 1) {
            $sequence = (int) $matches[1] + 1;
        }

        $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        while (SaleReturn::query()->where('return_number', $number)->exists()) {
            $sequence++;
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        }

        return $number;
    }

    private function cashierName(): string
    {
        $name = session('admin.name');

        return is_string($name) && $name !== '' ? $name : '—';
    }

    private function uniqueOrderNumber(string $number): string
    {
        if (! Schema::hasTable('orders')) {
            return $number;
        }

        $base = $number;
        $suffix = 2;

        while (Order::query()->where('order_number', $number)->exists()) {
            $number = $base.'-'.$suffix;
            $suffix++;
        }

        return $number;
    }

    private function allowsNegativeStock(): bool
    {
        $settings = session('admin.settings', []);

        return (bool) ($settings['allow_negative_stock'] ?? false);
    }

    private function actorUserId(): ?string
    {
        $email = session('admin.email');

        if (! is_string($email) || $email === '' || ! Schema::hasTable('users')) {
            return null;
        }

        $id = User::query()->where('email', $email)->value('id');

        return is_string($id) ? $id : null;
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    private function recordAudit(string $action, ?Model $auditable, ?array $old, ?array $new): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => $this->actorUserId(),
            'action' => $action,
            'auditable_type' => $auditable === null ? null : $auditable::class,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
