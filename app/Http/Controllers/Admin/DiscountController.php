<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DiscountType;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\Price;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.discounts.index', [
            'discounts' => AdminList::apply($store->discountRecords(), ['name', 'type', 'value', 'status', 'starts_at', 'ends_at', 'created_at']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'base_price']),
            'variants' => ProductVariant::query()->orderBy('sku')->get(['id', 'sku', 'size', 'color']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'types' => DiscountType::cases(),
        ]);
    }

    public function store(Request $request, AdminStore $store): RedirectResponse
    {
        $store->createDiscount($this->validated($request));

        return redirect()
            ->route('admin.discounts.index')
            ->with('status', __('admin.toast.discount_created'));
    }

    public function update(Request $request, Discount $discount, AdminStore $store): RedirectResponse
    {
        abort_if($store->updateDiscount($discount->id, $this->validated($request)) === null, 404);

        return redirect()
            ->route('admin.discounts.index')
            ->with('status', __('admin.toast.discount_updated'));
    }

    public function toggle(Discount $discount, AdminStore $store): RedirectResponse
    {
        abort_unless($store->toggleDiscount($discount->id), 404);

        return redirect()
            ->route('admin.discounts.index')
            ->with('status', __('admin.toast.discount_updated'));
    }

    public function destroy(Discount $discount, AdminStore $store): RedirectResponse
    {
        abort_unless($store->deleteDiscount($discount->id), 404);

        return redirect()
            ->route('admin.discounts.index')
            ->with('status', __('admin.toast.discount_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validator = ValidatorFacade::make($request->all(), $this->rules(), [], $this->attributes());
        $validator->after(function (Validator $validator) use ($request): void {
            $this->afterValidation($validator, $request);
        });

        return $validator->validate();
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, string>
     */
    private function attributes(): array
    {
        return [
            'name' => __('admin.discounts.name'),
            'type' => __('admin.discounts.type'),
            'value' => __('admin.discounts.value'),
            'starts_at' => __('admin.discounts.starts_at'),
            'ends_at' => __('admin.discounts.ends_at'),
        ];
    }

    private function afterValidation(Validator $validator, Request $request): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $type = $request->string('type')->toString();
        $value = (float) $request->input('value');

        if ($type === DiscountType::Percent->value && ($value < 0 || $value > 100)) {
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
    }
}
