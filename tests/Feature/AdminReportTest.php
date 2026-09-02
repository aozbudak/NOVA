<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Illuminate\Support\Carbon;
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

    public function test_sales_report_renders_today_metrics_charts_and_product_table(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 09:15:00'));

        $response = $this->get(route('admin.reports.show', 'sales'));

        $response->assertOk();
        $response->assertSee('Sales reports');
        $response->assertSee('Today');
        $response->assertSee('This week');
        $response->assertSee('This month');
        $response->assertSee('Custom range');
        $response->assertSee('Total sales');
        $response->assertSee('Total orders');
        $response->assertSee('Total items sold');
        $response->assertSee('Average order value');
        $response->assertSee('₺6,757');
        $response->assertSee('Sales trend');
        $response->assertSee('Sales by payment');
        $response->assertSee('Top products');
        $response->assertSee('Cash');
        $response->assertSee('Card');
        $response->assertSee('Other');
        $response->assertSee('Quantity sold');
        $response->assertSee('Net sales');
        $response->assertSee('Basic Shirt');
        $response->assertSee('Export');
        $response->assertSee('CSV');
        $response->assertSee('Excel');
        $response->assertSee('PDF');
    }

    public function test_inventory_report_renders_stock_metrics_filters_and_status_table(): void
    {
        $response = $this->get(route('admin.reports.show', 'inventory'));

        $response->assertOk();
        $response->assertSee('Total products');
        $response->assertSee('Total stock');
        $response->assertSee('Low stock products');
        $response->assertSee('Out of stock products');
        $response->assertSee('Stock value');
        $response->assertSee('Min stock');
        $response->assertSee('Fluid Silk Midi Dress');
        $response->assertSee('NOVA06-IVY-S');
        $response->assertSee('Out of stock');
        $response->assertSee('Category');
        $response->assertSee('Brand');
    }

    public function test_return_report_renders_rate_and_most_returned_product(): void
    {
        $response = $this->get(route('admin.reports.show', 'returns'));

        $response->assertOk();
        $response->assertSee('Total returns');
        $response->assertSee('Return rate');
        $response->assertSee('Return value');
        $response->assertSee('Most returned products');
        $response->assertSee('Cotton T-Shirt');
        $response->assertSee('50.0%');
        $response->assertSee('₺1,588');
        $response->assertSee('Main reason');
        $response->assertSee('Wrong size');
    }

    public function test_cash_report_renders_balances_for_selected_date(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 09:15:00'));

        $response = $this->get(route('admin.reports.show', 'cash'));

        $response->assertOk();
        $response->assertSee('Opening balance');
        $response->assertSee('Sales');
        $response->assertSee('Income');
        $response->assertSee('Expenses');
        $response->assertSee('Refunds');
        $response->assertSee('Closing balance');
        $response->assertSee('₺12,000');
        $response->assertSee('₺4,258');
        $response->assertSee('₺15,638');
        $response->assertSee('Cash movements');
        $response->assertSee('NOVA-1024');
    }

    public function test_customer_report_renders_value_metrics_and_top_buyers(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 09:15:00'));

        $response = $this->get(route('admin.reports.show', 'customers'));

        $response->assertOk();
        $response->assertSee('Total customers');
        $response->assertSee('New customers');
        $response->assertSee('Active customers');
        $response->assertSee('Total customer sales');
        $response->assertSee('Average customer value');
        $response->assertSee('Selin Arslan');
        $response->assertSee('₺94,590');
        $response->assertSee('₺41,250');
    }

    public function test_supplier_report_renders_purchase_totals(): void
    {
        $response = $this->get(route('admin.reports.show', 'suppliers'));

        $response->assertOk();
        $response->assertSee('Total suppliers');
        $response->assertSee('Total purchases');
        $response->assertSee('Top suppliers');
        $response->assertSee('Atelier Mills');
        $response->assertSee('₺224,600');
        $response->assertSee('₺186,400');
    }

    public function test_sales_report_csv_export_downloads_file(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 09:15:00'));

        $response = $this->get(route('admin.reports.export', ['report' => 'sales', 'format' => 'csv']));

        $response->assertDownload('sales-report.csv');
        $this->assertStringContainsString('Total sales', $response->streamedContent());
        $this->assertStringContainsString('Basic Shirt', $response->streamedContent());
    }

    public function test_export_excel_and_pdf_are_prepared_on_the_server(): void
    {
        $excel = $this->get(route('admin.reports.export', ['report' => 'inventory', 'format' => 'excel']));
        $excel->assertDownload('inventory-report.xls');
        $this->assertStringContainsString('Workbook', $excel->streamedContent());

        $pdf = $this->get(route('admin.reports.export', ['report' => 'suppliers', 'format' => 'pdf']));
        $pdf->assertDownload('suppliers-report.pdf');
        $this->assertStringStartsWith('%PDF', $pdf->streamedContent());
    }

    public function test_unknown_report_and_export_format_return_404(): void
    {
        $this->get(route('admin.reports.show', 'missing'))->assertNotFound();
        $this->get(route('admin.reports.export', ['report' => 'sales', 'format' => 'xml']))->assertNotFound();
    }

    public function test_cashier_is_forbidden_from_reports(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.reports.show', 'sales'))
            ->assertForbidden();
    }
}
