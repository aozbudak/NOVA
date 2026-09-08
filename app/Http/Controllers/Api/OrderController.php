<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CustomerAccount;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(CustomerAccount $account): JsonResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        return response()->json([
            'data' => $account->orderSummaries($customer),
        ]);
    }

    public function show(string $order, CustomerAccount $account): JsonResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        return response()->json([
            'data' => $account->detailOrder($account->order($customer, $order)),
        ]);
    }
}
