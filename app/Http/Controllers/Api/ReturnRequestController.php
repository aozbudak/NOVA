<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaleReturn;
use App\Support\CustomerAccount;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReturnRequestController extends Controller
{
    public function index(CustomerAccount $account): JsonResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        return response()->json([
            'data' => $account->returnSummaries($customer),
        ]);
    }

    public function store(Request $request, CustomerAccount $account, DatabaseRecords $records): JsonResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', Rule::in(SaleReturn::customerReasons())],
            'notes' => ['nullable', 'string', 'max:1000', 'required_if:reason,other'],
        ]);

        $order = $account->order($customer, $validated['order_id']);
        $notes = filled($validated['notes'] ?? null) ? trim((string) $validated['notes']) : null;

        $return = $records->requestReturn($customer, $order, $validated['reason'], $notes);

        return response()->json([
            'data' => $account->detailReturn($account->returnRequest($customer, $return->return_number)),
            'message' => __('storefront.account.return_submitted'),
        ], 201);
    }

    public function show(string $returnRequest, CustomerAccount $account): JsonResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return response()->json(['message' => __('auth.unauthenticated')], 401);
        }

        return response()->json([
            'data' => $account->detailReturn($account->returnRequest($customer, $returnRequest)),
        ]);
    }
}
