<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminExchangeTest extends TestCase
{
    public function test_exchanges_table_renders_original_new_and_differences(): void
    {
        $response = $this->get(route('admin.exchanges.index'));

        $response->assertOk();
        $response->assertSee('Exchanges');
        $response->assertSee('Original product');
        $response->assertSee('New product');
        $response->assertSee('EX-118');
        $response->assertSee('No difference');
        $response->assertSee('Additional payment');
        $response->assertSee('Refund');
    }

    public function test_exchange_detail_renders_stock_swap_and_price_difference(): void
    {
        $response = $this->get(route('admin.exchanges.show', 'ex-118'));

        $response->assertOk();
        $response->assertSee('Cotton T-Shirt / Black / M');
        $response->assertSee('Cotton T-Shirt / Black / L');
        $response->assertSee('Returned to stock');
        $response->assertSee('Deducted from stock');
        $response->assertSee('No difference');
    }

    public function test_unknown_exchange_returns_404(): void
    {
        $this->get(route('admin.exchanges.show', 'missing'))->assertNotFound();
    }
}
