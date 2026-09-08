<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use App\Models\Brand;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_discounts_index_renders_the_management_table(): void
    {
        Discount::factory()->percent(20)->create(['name' => 'Autumn 20']);

        $this->get(route('admin.discounts.index'))
            ->assertOk()
            ->assertSee('Discount management')
            ->assertSee('Autumn 20')
            ->assertSee('Select product')
            ->assertSee('Select variant')
            ->assertSee('Select category')
            ->assertSee('Select brand');
    }

    public function test_cashier_is_forbidden_from_discounts(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.discounts.index'))
            ->assertForbidden();
    }

    public function test_creates_a_product_percent_discount(): void
    {
        $product = Product::factory()->create(['base_price' => 1000]);

        $this->post(route('admin.discounts.store'), [
            'name' => 'T-Shirt 20',
            'type' => 'percent',
            'value' => 20,
            'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addMonth()->format('Y-m-d\TH:i'),
            'status' => 'active',
            'product_ids' => [$product->id],
        ])->assertRedirect(route('admin.discounts.index'));

        $this->assertDatabaseHas('discounts', [
            'name' => 'T-Shirt 20',
            'type' => 'percent',
            'is_active' => true,
        ]);
        $this->assertTrue($product->discounts()->where('name', 'T-Shirt 20')->exists());

        $this->get(route('admin.discounts.index'))
            ->assertOk()
            ->assertSee('T-Shirt 20');
    }

    public function test_rejects_a_percent_over_100(): void
    {
        $product = Product::factory()->create();

        $this->from(route('admin.discounts.index'))
            ->post(route('admin.discounts.store'), [
                'name' => 'Invalid',
                'type' => 'percent',
                'value' => 150,
                'status' => 'active',
                'product_ids' => [$product->id],
            ])
            ->assertRedirect(route('admin.discounts.index'))
            ->assertSessionHasErrors('value');
    }

    public function test_rejects_a_discount_without_targets(): void
    {
        $this->from(route('admin.discounts.index'))
            ->post(route('admin.discounts.store'), [
                'name' => 'No target',
                'type' => 'percent',
                'value' => 10,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.discounts.index'))
            ->assertSessionHasErrors('product_ids');
    }

    public function test_rejects_a_fixed_amount_above_the_product_price(): void
    {
        $product = Product::factory()->create(['base_price' => 100]);

        $this->from(route('admin.discounts.index'))
            ->post(route('admin.discounts.store'), [
                'name' => 'Too much',
                'type' => 'fixed',
                'value' => 500,
                'status' => 'active',
                'product_ids' => [$product->id],
            ])
            ->assertRedirect(route('admin.discounts.index'))
            ->assertSessionHasErrors('value');
    }

    public function test_public_api_lists_only_active_discounts(): void
    {
        Discount::factory()->percent(20)->create(['name' => 'Live']);
        Discount::factory()->percent(10)->inactive()->create(['name' => 'Off']);

        $this->getJson(route('api.discounts.index'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Live')
            ->assertJsonMissing(['name' => 'Off']);
    }

    public function test_api_creates_a_brand_discount(): void
    {
        $brand = Brand::factory()->create();

        $this->postJson(route('api.discounts.store'), [
            'name' => 'Nike 15',
            'type' => 'percent',
            'value' => 15,
            'status' => 'active',
            'brand_ids' => [$brand->id],
        ])->assertCreated();

        $this->assertTrue($brand->discounts()->where('name', 'Nike 15')->exists());
    }
}
