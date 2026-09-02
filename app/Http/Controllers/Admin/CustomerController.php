<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.customers.index', [
            'customers' => $store->customers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        return redirect()
            ->route('admin.customers.index')
            ->with('status', __('admin.toast.customer_created'));
    }

    public function show(string $customer, AdminStore $store): View
    {
        $record = $store->customer($customer);

        abort_if($record === null, 404);

        return view('admin.customers.show', [
            'customer' => $record,
        ]);
    }
}
