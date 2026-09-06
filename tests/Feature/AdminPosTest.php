<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminPosTest extends TestCase
{
    public function test_pos_screen_renders_barcode_search_cart_and_tenders(): void
    {
        $response = $this->get(route('admin.pos.index'));

        $response->assertOk();
        $response->assertSee('Search / barcode');
        $response->assertSee('data-pos-search', false);
        $response->assertSee('Cart');
        $response->assertSee('Subtotal');
        $response->assertSee('Cash');
        $response->assertSee('Card');
        $response->assertSee('Other');
        $response->assertSee('data-pos-note', false);
        $response->assertSee('Basic Shirt');
    }
}
