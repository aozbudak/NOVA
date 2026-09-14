<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StorefrontCoverSlot;
use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\Catalog;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(AdminStore $store, Catalog $catalog): View
    {
        $categories = $store->categoryRecords();

        return view('admin.categories.index', [
            'categories' => AdminList::apply($categories, ['name', 'products', 'stock', 'status']),
            'headerCategories' => $categories->where('show_in_header', true)->values(),
            'availableHeaderCategories' => $categories->where('show_in_header', false)->values(),
            'parents' => $store->categoryOptions()->whereNull('parent_id')->values(),
            'covers' => $catalog->coverSlots(),
        ]);
    }

    public function store(Request $request, AdminStore $store): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'string', 'max:36'],
            'parent_ids' => ['nullable', 'array'],
            'parent_ids.*' => ['string', 'max:36'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $store->createCategory($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('admin.toast.category_created'));
    }

    public function update(Request $request, string $category, AdminStore $store): RedirectResponse
    {
        abort_if($store->categoryRecords()->firstWhere('id', $category) === null, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'string', 'max:36'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        abort_if($store->updateCategory($category, $data) === null, 404);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('admin.toast.category_updated'));
    }

    public function destroy(string $category, AdminStore $store): RedirectResponse
    {
        abort_if($store->categoryRecords()->firstWhere('id', $category) === null, 404);

        if (! $store->deleteCategory($category)) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', __('admin.categories.delete_has_products'));
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('admin.toast.category_deleted'));
    }

    public function attachHeader(Request $request, AdminStore $store): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'string', 'max:36'],
        ]);

        abort_if($store->setCategoryHeader($data['category_id'], true) === null, 404);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('admin.toast.category_header_updated'));
    }

    public function detachHeader(string $category, AdminStore $store): RedirectResponse
    {
        abort_if($store->setCategoryHeader($category, false) === null, 404);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('admin.toast.category_header_updated'));
    }

    public function updateCovers(Request $request, DatabaseRecords $records): RedirectResponse
    {
        abort_unless(Schema::hasTable('storefront_covers'), 404);

        $coverRules = collect(StorefrontCoverSlot::values())
            ->mapWithKeys(fn (string $slot): array => [
                'covers.'.$slot => ['nullable', 'image', 'mimes:jpeg,jpg,webp,png', 'max:4096'],
            ])
            ->all();

        $request->validate([
            'covers' => ['required', 'array'],
            ...$coverRules,
        ]);

        $saved = false;

        foreach (StorefrontCoverSlot::cases() as $slot) {
            $file = $request->file('covers.'.$slot->value);

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $records->saveStorefrontCover($slot, $file);
            $saved = true;
        }

        if (! $saved) {
            throw ValidationException::withMessages([
                'covers' => __('admin.categories.cover_required'),
            ]);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('admin.toast.covers_updated'));
    }
}
