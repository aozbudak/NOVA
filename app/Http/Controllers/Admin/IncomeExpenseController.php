<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
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
            'transactions' => $store->incomeExpenses($filters),
            'categories' => $store->incomeExpenseCategories(),
            'cashiers' => $store->cashiers(),
            'filters' => $filters,
        ]);
    }

    public function create(AdminStore $store): View
    {
        return view('admin.income-expense.form', [
            'categories' => $store->incomeExpenseCategories(),
        ]);
    }

    public function store(): RedirectResponse
    {
        return redirect()
            ->route('admin.income-expense.index')
            ->with('status', __('admin.toast.transaction_saved'));
    }
}
