<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    public function store(Request $request, AdminStore $store, DatabaseRecords $records): JsonResponse
    {
        $skuRule = Schema::hasTable('product_variants')
            ? Rule::exists('product_variants', 'sku')
            : Rule::in($store->variants()->pluck('sku')->all());

        $validated = $request->validate([
            'payment' => ['required', 'in:cash,card,other'],
            'customer_id' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:255', 'required_if:payment,other'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string', $skuRule],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ], [
            'note.required_if' => __('admin.pos.note_required'),
        ]);

        $validated['note'] = filled($validated['note'] ?? null) ? trim((string) $validated['note']) : null;

        if ($validated['payment'] === 'other' && $validated['note'] === null) {
            throw ValidationException::withMessages([
                'note' => __('admin.pos.note_required'),
            ]);
        }

        $order = $records->placeSale($validated, collect(), '');

        if ($order instanceof Order) {
            return response()->json([
                'data' => $this->orderPayload($order, $validated['payment'], $validated['note']),
                'message' => __('admin.toast.sale_completed'),
            ], 201);
        }

        $variants = $store->variants()->keyBy('sku');
        $lines = collect($validated['items'])->map(function (array $item) use ($variants): array {
            $variant = $variants[$item['sku']];
            $quantity = (int) $item['quantity'];
            $discount = (float) ($item['discount'] ?? 0);
            $unit = (float) $variant['price'];
            $gross = $unit * $quantity;

            if ($discount > $gross) {
                throw ValidationException::withMessages([
                    'items' => __('admin.pos.discount_exceeds'),
                ]);
            }

            $available = (int) ($variant['stock'] ?? 0);

            if ($quantity > $available) {
                throw ValidationException::withMessages([
                    'items' => __('admin.pos.insufficient_stock'),
                ]);
            }

            return [
                'product' => $variant['product'],
                'variant' => $variant['color'].' / '.$variant['size'],
                'sku' => $variant['sku'],
                'qty' => $quantity,
                'unit' => $unit,
                'discount' => $discount,
                'total' => $gross - $discount,
            ];
        });

        $number = 'NV-'.now()->format('Ymd').'-0001';

        return response()->json([
            'data' => [
                'id' => $number,
                'number' => $number,
                'payment' => $validated['payment'],
                'customer_id' => $validated['customer_id'] ?? null,
                'note' => $validated['note'],
                'items' => $lines->all(),
                'subtotal' => $lines->sum(fn (array $line): float => $line['unit'] * $line['qty']),
                'discount' => $lines->sum('discount'),
                'total' => $lines->sum('total'),
                'status' => 'completed',
            ],
            'message' => __('admin.toast.sale_completed'),
        ], 201);
    }

    public function show(string $sale, AdminStore $store): JsonResponse
    {
        $record = $store->sale($sale);

        abort_if($record === null, 404);

        return response()->json(['data' => $record]);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order, string $payment, ?string $note = null): array
    {
        $order->loadMissing('items');

        $items = $order->items->map(fn ($item): array => [
            'product' => $item->product_name,
            'variant' => trim(($item->color ?? '').' / '.($item->size ?? ''), ' /'),
            'sku' => $item->sku,
            'qty' => (int) $item->quantity,
            'unit' => (float) $item->unit_price,
            'discount' => (float) $item->discount_amount,
            'total' => (float) $item->total_price,
        ]);

        return [
            'id' => $order->order_number,
            'number' => $order->order_number,
            'payment' => $payment,
            'customer_id' => $order->customer_id,
            'note' => $note,
            'items' => $items->all(),
            'subtotal' => (float) $order->subtotal,
            'discount' => (float) $order->discount_amount,
            'total' => (float) $order->total_amount,
            'status' => $order->status,
        ];
    }
}
