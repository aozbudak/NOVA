<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(AdminStore $store): JsonResponse
    {
        return response()->json(['data' => $store->categoryRecords()->values()]);
    }

    public function store(Request $request, AdminStore $store): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'string', 'max:36'],
            'parent_ids' => ['nullable', 'array'],
            'parent_ids.*' => ['string', 'max:36'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $store->createCategory($data);

        return response()->json([
            'status' => 'created',
            'message' => __('admin.toast.category_created'),
        ], 201);
    }

    public function update(Request $request, string $category, AdminStore $store): JsonResponse
    {
        abort_if($store->categoryRecords()->firstWhere('id', $category) === null, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'string', 'max:36'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        abort_if($store->updateCategory($category, $data) === null, 404);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.category_updated'),
        ]);
    }

    public function destroy(string $category, AdminStore $store): JsonResponse
    {
        abort_if($store->categoryRecords()->firstWhere('id', $category) === null, 404);

        if (! $store->deleteCategory($category)) {
            return response()->json([
                'status' => 'error',
                'message' => __('admin.categories.delete_has_products'),
            ], 422);
        }

        return response()->json([
            'status' => 'deleted',
            'message' => __('admin.toast.category_deleted'),
        ]);
    }

    public function attachHeader(Request $request, AdminStore $store): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'string', 'max:36'],
        ]);

        abort_if($store->setCategoryHeader($data['category_id'], true) === null, 404);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.category_header_updated'),
        ]);
    }

    public function detachHeader(string $category, AdminStore $store): JsonResponse
    {
        abort_if($store->setCategoryHeader($category, false) === null, 404);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.category_header_updated'),
        ]);
    }
}
