<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeExpenseController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'type' => $request->string('type')->toString(),
            'category' => $request->string('category')->toString(),
            'date' => $request->string('date')->toString(),
            'user' => $request->string('user')->toString(),
        ];

        return view('admin.income-expense.index', [
            'transactions' => AdminList::apply($store->incomeExpenses($filters), ['date', 'type', 'amount']),
            'categories' => $store->incomeExpenseCategories(),
            'cashiers' => $store->cashiers(),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'type' => [
                    'label' => __('admin.income_expense.filter_type'),
                    'value' => $filters['type'] === '' ? '' : __('admin.income_expense.'.$filters['type']),
                ],
                'category' => ['label' => __('admin.income_expense.filter_category')],
                'date' => ['label' => __('admin.income_expense.filter_date')],
                'user' => ['label' => __('admin.income_expense.filter_user')],
            ]),
        ]);
    }

    public function create(AdminStore $store): View
    {
        return view('admin.income-expense.form', [
            'categories' => $store->incomeExpenseCategories(),
        ]);
    }

    public function store(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'category' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $records->recordIncomeExpense(
            $validated['type'],
            $validated['category'],
            $validated['description'],
            (float) $validated['amount'],
        );

        return redirect()
            ->route('admin.income-expense.index')
            ->with('status', __('admin.toast.transaction_saved'));
    }
}
