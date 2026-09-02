<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.customers.index', [
            'customers' => $store->customers(),
        ]);
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
