<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminReturnTest extends TestCase
{
    public function test_returns_table_renders_create_action_and_columns(): void
    {
        $response = $this->get(route('admin.returns.index'));

        $response->assertOk();
        $response->assertSee('Returns');
        $response->assertSee('Create return');
        $response->assertSee('RT-2204');
        $response->assertSee('NV-10311');
        $response->assertSee('Wrong size');
    }

    public function test_create_return_looks_up_sale_and_lists_items(): void
    {
        $response = $this->get(route('admin.returns.create', ['sale' => 'NOVA-1024']));

        $response->assertOk();
        $response->assertSee('Basic Shirt');
        $response->assertSee('White / M');
        $response->assertSee('₺899');
        $response->assertSee('Tailored Trouser');
        $response->assertSee('₺1,499');
        $response->assertSee('Wrong size');
        $response->assertSee('Defective product');
        $response->assertSee('Customer changed mind');
        $response->assertSee('Wrong product');
        $response->assertDontSee('Full return');
        $response->assertDontSee('Partial return');
    }

    public function test_return_detail_renders_stock_refund_and_cash_links(): void
    {
        $response = $this->get(route('admin.returns.show', 'rt-2204'));

        $response->assertOk();
        $response->assertSee('RT-2204');
        $response->assertSee('Return record');
        $response->assertSee('Stock movement');
        $response->assertSee('Refund');
        $response->assertSee('Cash movement');
        $response->assertSee('Cotton T-Shirt');
    }

    public function test_unknown_return_returns_404(): void
    {
        $this->get(route('admin.returns.show', 'missing'))->assertNotFound();
    }
}
