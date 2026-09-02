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
        $response->assertSee('Saving...');
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

    public function test_empty_filters_render_no_products_found(): void
    {
        $response = $this->get(route('admin.products.index', ['search' => 'no-such-sku']));

        $response->assertOk();
        $response->assertSee('No products found');
        $response->assertSee('Try changing your filters or add a new product.');
        $response->assertSee('Add product');
        $response->assertDontSee('Basic Shirt');
    }

    public function test_products_table_uses_deactivate_instead_of_delete(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('Deactivate product?');
        $response->assertSee(route('admin.products.deactivate', 'basic-shirt'), false);
    }

    public function test_create_redirects_with_a_success_toast(): void
    {
        $this->post(route('admin.products.store'))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'Product created successfully.');
    }

    public function test_deactivate_redirects_with_a_toast(): void
    {
        $this->post(route('admin.products.deactivate', 'basic-shirt'))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'Product deactivated successfully.');
    }

    public function test_unknown_product_deactivate_returns_404(): void
    {
        $this->post(route('admin.products.deactivate', 'missing'))->assertNotFound();
    }

    public function test_variants_index_lists_sku_barcode_and_stock(): void
    {
        $response = $this->get(route('admin.variants.index'));

        $response->assertOk();
        $response->assertSee('NOVA03-BLA-XS');
        $response->assertSee('Barcode');
    }
}
