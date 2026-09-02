<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function index(Request $request, AdminStore $store): JsonResponse
    {
        return response()->json([
            'data' => $store->sales([
                'search' => $request->string('search')->toString(),
                'from' => $request->string('from')->toString(),
                'to' => $request->string('to')->toString(),
                'payment' => $request->string('payment')->toString(),
                'cashier' => $request->string('cashier')->toString(),
                'status' => $request->string('status')->toString(),
            ])->values(),
        ]);
    }

    public function store(Request $request, AdminStore $store): JsonResponse
    {
        $skus = $store->variants()->pluck('sku')->all();

        $validated = $request->validate([
            'payment' => ['required', 'in:cash,card,other'],
            'customer_id' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string', Rule::in($skus)],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $variants = $store->variants()->keyBy('sku');
        $lines = collect($validated['items'])->map(function (array $item) use ($variants): array {
            $variant = $variants[$item['sku']];
            $quantity = (int) $item['quantity'];
            $discount = (float) ($item['discount'] ?? 0);
            $unit = (float) $variant['price'];

            return [
                'product' => $variant['product'],
                'variant' => $variant['color'].' / '.$variant['size'],
                'sku' => $variant['sku'],
                'qty' => $quantity,
                'unit' => $unit,
                'discount' => $discount,
                'total' => ($unit * $quantity) - $discount,
            ];
        });

        $number = 'NV-'.now()->format('ymdHis');

        return response()->json([
            'data' => [
                'id' => strtolower($number),
                'number' => $number,
                'payment' => $validated['payment'],
                'customer_id' => $validated['customer_id'] ?? null,
                'items' => $lines->all(),
                'subtotal' => $lines->sum(fn (array $line): float => $line['unit'] * $line['qty']),
                'discount' => $lines->sum('discount'),
                'total' => $lines->sum('total'),
                'status' => 'completed',
            ],
            'message' => __('admin.toast.transaction_saved'),
        ], 201);
    }

    public function show(string $sale, AdminStore $store): JsonResponse
    {
        $record = $store->sale($sale);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }
}
