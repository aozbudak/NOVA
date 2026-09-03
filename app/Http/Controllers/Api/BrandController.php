<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use App\Support\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Catalog $catalog): JsonResponse
    {
        return response()->json([
            'data' => $catalog->brands()->map(fn ($brand): array => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'logo' => $brand->logo,
            ])->values(),
        ]);
    }

    public function store(Request $request, AdminStore $store): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $store->createBrand($data);

        return response()->json([
            'status' => 'created',
            'message' => __('admin.toast.brand_created'),
        ], 201);
    }

    public function update(Request $request, string $brand, AdminStore $store): JsonResponse
    {
        abort_if($store->brandRecords()->firstWhere('id', $brand) === null
            && $store->brandRecords()->firstWhere('slug', $brand) === null, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        abort_if($store->updateBrand($brand, $data) === null, 404);

        return response()->json([
            'status' => 'updated',
            'message' => __('admin.toast.brand_updated'),
        ]);
    }

    public function destroy(string $brand, AdminStore $store): JsonResponse
    {
        abort_unless($store->deleteBrand($brand), 404);

        return response()->json([
            'status' => 'deleted',
            'message' => __('admin.toast.brand_deleted'),
        ]);
    }
}
