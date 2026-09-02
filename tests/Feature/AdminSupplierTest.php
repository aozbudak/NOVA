<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminSupplierTest extends TestCase
{
    public function test_suppliers_table_renders_toolbar_and_statuses(): void
    {
        $response = $this->get(route('admin.suppliers.index'));

        $response->assertOk();
        $response->assertSee('Suppliers');
        $response->assertSee('Add supplier');
        $response->assertSee('Contact person');
        $response->assertSee('Total purchases');
        $response->assertSee('Last purchase');
        $response->assertSee('Atelier Mills');
        $response->assertSee('Active');
        $response->assertSee('Inactive');
    }

    public function test_supplier_detail_renders_purchases_and_linked_stock_movement(): void
    {
        $response = $this->get(route('admin.suppliers.show', 'atelier-mills'));

        $response->assertOk();
        $response->assertSee('General information');
        $response->assertSee('Purchase history');
        $response->assertSee('PO-441');
        $response->assertSee('Summary');
        $response->assertSee('Outstanding balance');
        $response->assertSee('Linked stock movements');
        $response->assertSee('Tailored Trouser');
    }

    public function test_unknown_supplier_returns_404(): void
    {
        $this->get(route('admin.suppliers.show', 'missing'))->assertNotFound();
    }

    public function test_cashier_is_forbidden_from_suppliers(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.suppliers.index'))
            ->assertForbidden();
    }
}
