<?php

namespace App\Http\Controllers\Api;

use App\Enums\DiscountType;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Product;
use App\Support\AdminStore;
use App\Support\Price;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DiscountController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Discount::query()
                ->active()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->map(fn (Discount $discount): array => [
                    'id' => $discount->id,
                    'name' => $discount->name,
                    'type' => $discount->type->value,
                    'value' => (float) $discount->value,
                    'starts_at' => $discount->starts_at?->toIso8601String(),
                    'ends_at' => $discount->ends_at?->toIso8601String(),
                ])
                ->values(),
        ]);
    }

    public function store(Request $request, AdminStore $store): JsonResponse
    {
        $store->createDiscount($this->validated($request));

        return response()->json([
            'status' => 'created',
            'message' => __('admin.toast.discount_created'),
        ], 201);
    }

    public function update(Request $request, Discount $discount, AdminStore $store): JsonResponse
    {
        abort_if($store->updateDiscount($discount->id, $this->validated($request)) === null, 404);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.discount_updated'),
        ]);
    }

    public function destroy(Discount $discount, AdminStore $store): JsonResponse
    {
        abort_unless($store->deleteDiscount($discount->id), 404);

        return response()->json([
            'status' => 'deleted',
            'message' => __('admin.toast.discount_deleted'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validator = ValidatorFacade::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(DiscountType::class)],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['uuid', 'exists:products,id'],
            'variant_ids' => ['nullable', 'array'],
            'variant_ids.*' => ['uuid', 'exists:product_variants,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['uuid', 'exists:categories,id'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['uuid', 'exists:brands,id'],
        ]);

        $validator->after(function (Validator $validator) use ($request): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $type = $request->string('type')->toString();
            $value = (float) $request->input('value');

            if ($type === DiscountType::Percent->value && $value > 100) {
                $validator->errors()->add('value', __('admin.discounts.percent_range'));
            }

            $productIds = $request->input('product_ids', []);
            $variantIds = $request->input('variant_ids', []);
            $categoryIds = $request->input('category_ids', []);
            $brandIds = $request->input('brand_ids', []);

            if ($productIds === [] && $variantIds === [] && $categoryIds === [] && $brandIds === []) {
                $validator->errors()->add('product_ids', __('admin.discounts.target_required'));
            }

            if ($type === DiscountType::Fixed->value && is_array($productIds) && $productIds !== []) {
                $min = Product::query()->whereIn('id', $productIds)->min('base_price');

                if ($min !== null && bccomp(Price::money($value), Price::money($min), 2) > 0) {
                    $validator->errors()->add('value', __('admin.discounts.fixed_exceeds'));
                }
            }
        });

        return $validator->validate();
    }
}
