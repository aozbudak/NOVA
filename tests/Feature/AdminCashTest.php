<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminCashTest extends TestCase
{
    public function test_cash_register_renders_summary_and_actions(): void
    {
        $response = $this->get(route('admin.cash.index'));

        $response->assertOk();
        $response->assertSee('Cash register');
        $response->assertSee('Opening balance');
        $response->assertSee('Current balance');
        $response->assertSee("Today's sales");
        $response->assertSee("Today's expenses");
        $response->assertSee("Today's refunds");
        $response->assertSee('Open register');
        $response->assertSee('Close register');
    }

    public function test_open_register_form_collects_balance_date_and_user(): void
    {
        $response = $this->get(route('admin.cash.open'));

        $response->assertOk();
        $response->assertSee('Opening balance');
        $response->assertSee('Date');
        $response->assertSee('User');
        $response->assertSee('Ayşe Yılmaz');
    }

    public function test_close_register_renders_expected_actual_and_difference(): void
    {
        $response = $this->get(route('admin.cash.close'));

        $response->assertOk();
        $response->assertSee('Expected balance');
        $response->assertSee('Actual balance');
        $response->assertSee('Difference');
        $response->assertSee('₺55,290');
    }

    public function test_cash_movements_render_each_operation_type(): void
    {
        $response = $this->get(route('admin.cash.movements'));

        $response->assertOk();
        $response->assertSee('Cash movements');
        $response->assertSee('Sale');
        $response->assertSee('Refund');
        $response->assertSee('Expense');
        $response->assertSee('Income');
        $response->assertSee('Opening');
        $response->assertSee('Closing');
        $response->assertSee('Adjustment');
        $response->assertSee('NOVA-1024');
    }

    public function test_cashier_is_forbidden_from_cash(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.cash.index'))
            ->assertForbidden();
    }
}
