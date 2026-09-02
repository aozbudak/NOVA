<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    public function test_customers_table_renders_spend_and_last_purchase(): void
    {
        $response = $this->get(route('admin.customers.index'));

        $response->assertOk();
        $response->assertSee('Elif Kaya');
        $response->assertSee('Total orders');
        $response->assertSee('Total spent');
        $response->assertSee('Last purchase');
        $response->assertSee('₺24,800');
    }

    public function test_customer_detail_renders_profile_sales_and_returns(): void
    {
        $response = $this->get(route('admin.customers.show', 'elif-kaya'));

        $response->assertOk();
        $response->assertSee('Elif Kaya');
        $response->assertSee('Profile');
        $response->assertSee('Contact');
        $response->assertSee('Sales history');
        $response->assertSee('Return history');
        $response->assertSee('NV-10482');
        $response->assertSee('RT-2204');
    }

    public function test_unknown_customer_returns_404(): void
    {
        $this->get(route('admin.customers.show', 'missing'))->assertNotFound();
    }
}
