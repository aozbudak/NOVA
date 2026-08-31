<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontProductTest extends TestCase
{
    public function test_product_page_renders_gallery_and_size_options(): void
    {
        $response = $this->get(route('product.show', 'structured-wool-coat'));

        $response->assertOk();
        $response->assertSee('Structured Wool Coat');
        $response->assertSee('Add to bag');
        $response->assertSee('XS');
        $response->assertSee('Product details');
    }

    public function test_unknown_product_returns_404(): void
    {
        $this->get(route('product.show', 'does-not-exist'))->assertNotFound();
    }

    public function test_out_of_stock_size_is_disabled(): void
    {
        $response = $this->get(route('product.show', 'wide-leg-wool-trousers'));

        $response->assertOk();
        $response->assertSee('line-through');
    }
}
