<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminReportTest extends TestCase
{
    public function test_reports_hub_lists_each_category(): void
    {
        $response = $this->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Reports');
        $response->assertSee('Sales reports');
        $response->assertSee('Product reports');
        $response->assertSee('Inventory reports');
        $response->assertSee('Cash reports');
        $response->assertSee('Returns reports');
        $response->assertSee('Customer reports');
        $response->assertSee('Supplier reports');
    }

    public function test_sales_report_page_renders_metrics(): void
    {
        $response = $this->get(route('admin.reports.show', 'sales'));

        $response->assertOk();
        $response->assertSee('Sales reports');
        $response->assertSee('₺48,250');
        $response->assertSee('126');
    }

    public function test_unknown_report_returns_404(): void
    {
        $this->get(route('admin.reports.show', 'missing'))->assertNotFound();
    }
}
