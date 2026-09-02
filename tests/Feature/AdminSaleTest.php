<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminSaleTest extends TestCase
{
    public function test_sales_table_renders_filters_and_statuses(): void
    {
        $response = $this->get(route('admin.sales.index'));

        $response->assertOk();
        $response->assertSee('Sales');
        $response->assertSee('NOVA-1024');
        $response->assertSee('Completed');
        $response->assertSee('Cancelled');
        $response->assertSee('Returned');
        $response->assertSee('Partially returned');
        $response->assertSee('Elif Kaya');
    }

    public function test_sale_detail_renders_number_items_payment_and_effects(): void
    {
        $response = $this->get(route('admin.sales.show', 'nova-1024'));

        $response->assertOk();
        $response->assertSee('SALE #NOVA-1024');
        $response->assertSee('Sale number');
        $response->assertSee('Ayşe Yılmaz');
        $response->assertSee('Basic Shirt');
        $response->assertSee('NOVA01-WHI-M');
        $response->assertSee('Payment information');
        $response->assertSee('Stock effects');
        $response->assertSee('Cash effects');
        $response->assertSee('Subtotal');
    }

    public function test_unknown_sale_returns_404(): void
    {
        $this->get(route('admin.sales.show', 'missing'))->assertNotFound();
    }
}
