<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminIncomeExpenseTest extends TestCase
{
    public function test_income_expense_table_renders_add_action_and_types(): void
    {
        $response = $this->get(route('admin.income-expense.index'));

        $response->assertOk();
        $response->assertSee('Income & expenses');
        $response->assertSee('Add transaction');
        $response->assertSee('Packaging supplies');
        $response->assertSee('Alteration fee');
        $response->assertSee('Income');
        $response->assertSee('Expense');
    }

    public function test_add_transaction_form_selects_income_or_expense(): void
    {
        $response = $this->get(route('admin.income-expense.create'));

        $response->assertOk();
        $response->assertSee('Income');
        $response->assertSee('Expense');
        $response->assertSee('Category');
    }
}
