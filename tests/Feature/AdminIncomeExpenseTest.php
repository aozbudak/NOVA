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
        $response->assertSee('Packaging');
        $response->assertSee('Custom category');
    }

    public function test_category_dropdown_uses_turkish_labels(): void
    {
        $this->withSession([
            'admin.authenticated' => true,
            'locale' => 'tr',
        ]);

        $response = $this->get(route('admin.income-expense.create'));

        $response->assertOk();
        $response->assertSee('Ambalaj');
        $response->assertSee('Tadilat');
        $response->assertSee('Kargo');
        $response->assertSee('Faturalar');
        $response->assertSee('Diğer');
        $response->assertSee('Özel kategori');
    }

    public function test_custom_category_is_accepted_when_saving(): void
    {
        $this->from(route('admin.income-expense.create'))
            ->post(route('admin.income-expense.store'), [
                'type' => 'expense',
                'category' => 'Packaging',
                'custom_category' => 'Kira',
                'description' => 'Office rent',
                'amount' => 1500,
            ])
            ->assertRedirect(route('admin.income-expense.index'))
            ->assertSessionHas('status');
    }
}
