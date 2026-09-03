<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request, AdminStore $store): View
    {
        $filters = [
            'search' => $request->string('search')->toString(),
        ];
        $search = strtolower($filters['search']);
        $customers = $store->customers();

        if ($search !== '') {
            $customers = $customers->filter(function (array $customer) use ($search): bool {
                return str_contains(strtolower($customer['name'].' '.$customer['email'].' '.$customer['phone']), $search);
            })->values();
        }

        return view('admin.customers.index', [
            'customers' => AdminList::apply($customers, ['name', 'orders', 'spent', 'last_purchase']),
            'filters' => $filters,
            'chips' => AdminList::chips($filters, [
                'search' => ['label' => __('admin.common.search')],
            ]),
        ]);
    }

    public function store(Request $request, AdminStore $store): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $store->createCustomer($data);

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
