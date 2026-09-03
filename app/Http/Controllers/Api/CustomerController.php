<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->customers()->values()]);
    }

    public function store(Request $request, AdminStore $store): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $store->createCustomer($data);

        return response()->json([
            'status' => 'created',
            'message' => __('admin.toast.customer_created'),
        ], 201);
    }

    public function show(string $customer, AdminStore $store): JsonResponse
    {
        $record = $store->customer($customer);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }
}
