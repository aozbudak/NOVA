<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Catalog;
use Database\Seeders\AdminCatalogSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackendPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_product_create_persists_to_the_database(): void
    {
        $this->post(route('admin.products.store'), [
            'name' => 'Canvas Tote',
            'category' => 'Accessories',
            'brand' => 'NOVA',
            'price' => 1000,
            'vat' => 20,
            'status' => 'active',
            'initial_stock' => 12,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'canvas-tote')->first();
        $this->assertNotNull($product);
        $this->assertEquals(20, (float) $product->vat_rate);
        $this->assertSame(12, (int) $product->variants()->first()?->stock?->quantity);
        $this->assertSame('2000000000015', $product->variants()->first()?->barcode);
        $this->assertSame(0, $product->images()->count());
        $this->assertSame([], (new Catalog)->findBySlug('canvas-tote')['images']);
        $this->assertTrue(Brand::query()->where('name', 'NOVA')->exists());
        $this->assertDatabaseHas('stock_movements', [
            'movement_type' => 'adjustment_in',
            'quantity' => 12,
            'note' => 'Initial stock',
        ]);

        $this->get(route('product.show', 'canvas-tote'))
            ->assertOk()
            ->assertDontSee('photo-1521572163474', false);
    }

    public function test_admin_product_create_stores_uploaded_images(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('tote.jpg', 80, 100);

        $this->post(route('admin.products.store'), [
            'name' => 'Canvas Tote',
            'category' => 'Accessories',
            'brand' => 'NOVA',
            'price' => 1000,
            'images' => [$file],
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('slug', 'canvas-tote')->first();
        $this->assertNotNull($product);
        $this->assertSame(1, $product->images()->count());

        $url = (string) $product->images()->first()?->image_url;
        $this->assertStringContainsString('/storage/products/', $url);
        $this->assertStringNotContainsString('unsplash.com', $url);
        Storage::disk('public')->assertExists(Str::after($url, '/storage/'));

        $this->get(route('product.show', 'canvas-tote'))
            ->assertOk()
            ->assertSee($url, false);
    }

    public function test_admin_customer_and_supplier_creates_persist(): void
    {
        $this->post(route('admin.customers.store'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@nova.example',
            'phone' => '0532 000 00 01',
            'password' => 'password123',
        ])->assertRedirect(route('admin.customers.index'));

        $this->assertTrue(Customer::query()->where('slug', 'ada-lovelace')->exists());
        $this->assertTrue(User::query()->where('email', 'ada@nova.example')->exists());

        $this->post(route('admin.categories.store'), [
            'name' => 'Jackets',
            'status' => 'active',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertTrue(Category::query()->where('name', 'Jackets')->exists());

        $this->post(route('admin.suppliers.store'), [
            'name' => 'Harbor Mills',
            'contact' => 'Ece Yılmaz',
            'email' => 'ece@harbormills.example',
        ])->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'company_name' => 'Harbor Mills',
            'slug' => 'harbor-mills',
        ]);
    }

    public function test_inventory_adjust_and_pos_sale_update_stock_and_orders(): void
    {
        $this->seed(AdminCatalogSeeder::class);

        $variant = ProductVariant::query()->where('sku', 'NOVA01-WHI-S')->first();
        $this->assertNotNull($variant);
        $before = (int) $variant->stock?->quantity;

        $this->post(route('admin.suppliers.store'), [
            'name' => 'Harbor Mills',
            'contact' => 'Ece Yılmaz',
            'email' => 'ece@harbormills.example',
        ])->assertRedirect(route('admin.suppliers.index'));

        $supplier = Supplier::query()->where('slug', 'harbor-mills')->first();
        $this->assertNotNull($supplier);

        $this->post(route('admin.inventory.adjust'), [
            'sku' => 'NOVA01-WHI-S',
            'type' => 'in',
            'quantity' => 3,
            'note' => 'New delivery',
            'supplier_id' => 'harbor-mills',
        ])->assertRedirect(route('admin.inventory.index'));

        $this->assertSame($before + 3, (int) $variant->fresh()->stock?->quantity);
        $this->assertSame($variant->barcode, $variant->fresh()->barcode);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'movement_type' => 'purchase',
            'quantity' => 3,
            'supplier_id' => $supplier->id,
        ]);

        $this->get(route('admin.inventory.movements'))
            ->assertOk()
            ->assertSee('Harbor Mills');

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => 'NOVA01-WHI-S', 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertTrue(Order::query()->where('order_number', 'like', 'NV-%')->exists());
        $this->assertSame($before + 2, (int) $variant->fresh()->stock?->quantity);
    }

    public function test_stock_in_with_catalog_supplier_slug_persists(): void
    {
        $this->seed(AdminCatalogSeeder::class);

        $variant = ProductVariant::query()->where('sku', 'NOVA01-WHI-S')->first();
        $this->assertNotNull($variant);

        $this->post(route('admin.inventory.adjust'), [
            'sku' => 'NOVA01-WHI-S',
            'type' => 'in',
            'quantity' => 1,
            'supplier_id' => 'studio-textiles',
        ])->assertRedirect(route('admin.inventory.index'));

        $supplier = Supplier::query()->where('slug', 'studio-textiles')->first();
        $this->assertNotNull($supplier);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $variant->id,
            'movement_type' => 'purchase',
            'quantity' => 1,
            'supplier_id' => $supplier->id,
        ]);

        $this->getJson(route('api.inventory.index', ['supplier' => 'studio-textiles']))
            ->assertOk()
            ->assertJsonFragment([
                'sku' => 'NOVA01-WHI-S',
                'supplier' => 'Studio Textiles',
            ]);
    }

    public function test_stock_in_assigns_a_system_barcode_when_the_variant_has_none(): void
    {
        $variant = ProductVariant::factory()->create(['barcode' => null]);

        $this->post(route('admin.inventory.adjust'), [
            'sku' => $variant->sku,
            'type' => 'in',
            'quantity' => 1,
        ])->assertRedirect(route('admin.inventory.index'));

        $this->assertSame('2000000000015', $variant->fresh()->barcode);
    }

    public function test_stock_movements_list_groups_rows_by_product_name(): void
    {
        $alpha = Product::factory()->create(['name' => 'Alpha Coat']);
        $zeta = Product::factory()->create(['name' => 'Zeta Scarf']);
        $alphaVariant = ProductVariant::factory()->for($alpha)->create();
        $zetaVariant = ProductVariant::factory()->for($zeta)->create();

        StockMovement::query()->create([
            'product_variant_id' => $zetaVariant->id,
            'movement_type' => 'purchase',
            'quantity' => 4,
            'created_at' => now(),
        ]);

        StockMovement::query()->create([
            'product_variant_id' => $alphaVariant->id,
            'movement_type' => 'purchase',
            'quantity' => 2,
            'created_at' => now()->subHour(),
        ]);

        $this->get(route('admin.inventory.movements'))
            ->assertOk()
            ->assertSeeInOrder(['Alpha Coat', 'Zeta Scarf']);
    }

    public function test_checkout_and_return_persist_orders(): void
    {
        $this->seed(CatalogSeeder::class);
        $this->seed(AdminCatalogSeeder::class);

        $this->post(route('cart.store'), [
            'product_id' => 1,
            'size' => 'M',
            'quantity' => 1,
        ]);

        $this->post(route('checkout.store'), [
            'email' => 'client@nova.example',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'address' => '12 Atelier Street',
            'city' => 'Berlin',
            'postal_code' => '10115',
            'country' => 'Germany',
            'delivery' => 'standard',
            'payment' => 'card',
        ])->assertRedirect(route('checkout.confirmation'));

        $order = Order::query()->where('order_number', 'like', 'NOVA-%')->first();
        $this->assertNotNull($order);
        $this->assertSame('client@nova.example', $order->customer?->email);

        $this->postJson(route('api.returns.store'), [
            'sale' => $order->order_number,
            'reason' => 'size',
        ])->assertCreated();

        $this->assertTrue(SaleReturn::query()->where('order_id', $order->id)->exists());
        $this->assertSame('returned', $order->fresh()->status);
    }

    public function test_register_creates_a_user_and_customer(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('account.show'));

        $this->assertTrue(User::query()->where('email', 'ada@nova.example')->exists());
        $this->assertTrue(Customer::query()->where('email', 'ada@nova.example')->exists());

        $this->post(route('login.store'), [
            'email' => 'ada@nova.example',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_cash_open_and_close_persist_the_register(): void
    {
        $this->post(route('admin.cash.open.store'), [
            'opening' => 1500,
            'date' => '2026-09-02',
        ])->assertRedirect(route('admin.cash.index'));

        $this->assertTrue(CashRegister::query()->where('is_active', true)->exists());

        $this->post(route('admin.cash.close.store'), [
            'actual' => 1480,
        ])->assertRedirect(route('admin.cash.index'));

        $this->assertTrue(CashRegister::query()->whereNotNull('closed_at')->exists());
    }
}
