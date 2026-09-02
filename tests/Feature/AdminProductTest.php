<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminProductTest extends TestCase
{
    public function test_products_table_renders_sku_stock_badge_and_add_action(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('Products');
        $response->assertSee('Add product');
        $response->assertSee('Basic Shirt');
        $response->assertSee('NOVA01');
        $response->assertSee('Wool Coat');
        $response->assertSee('In stock');
        $response->assertSee('Low stock');
        $response->assertSee('Out of stock');
    }

    public function test_create_form_is_split_into_operational_sections(): void
    {
        $response = $this->get(route('admin.products.create'));

        $response->assertOk();
        $response->assertSee('Basic information');
        $response->assertSee('Pricing');
        $response->assertSee('Variants');
        $response->assertSee('Images');
        $response->assertSee('Inventory');
        $response->assertSee('Dashboard');
        $response->assertSee('Add product');
    }

    public function test_edit_form_loads_cotton_tshirt_size_and_color_matrix(): void
    {
        $response = $this->get(route('admin.products.edit', 'cotton-t-shirt'));

        $response->assertOk();
        $response->assertSee('Cotton T-Shirt');
        $response->assertSee('Black');
        $response->assertSee('White');
        $response->assertSee('XS');
        $response->assertSee('XL');
        $response->assertSee('NOVA03-BLA-M');
    }

    public function test_variants_index_lists_sku_barcode_and_stock(): void
    {
        $response = $this->get(route('admin.variants.index'));

        $response->assertOk();
        $response->assertSee('NOVA03-BLA-XS');
        $response->assertSee('Barcode');
    }
}
