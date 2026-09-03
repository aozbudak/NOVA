<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $parentId = filled($data['parent_id'] ?? null) ? (string) $data['parent_id'] : null;
        $exists = $store->categoryRecords()->contains(function (array $row) use ($data, $parentId): bool {
            $rowParent = filled($row['parent_id'] ?? null) ? (string) $row['parent_id'] : null;

            return strcasecmp($row['name'], $data['name']) === 0 && $rowParent === $parentId;
        });

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => __('validation.unique', ['attribute' => __('admin.categories.name')]),
            ]);
        }

        $store->createCategory($data);

        return response()->json([
            'status' => 'created',
            'message' => __('admin.toast.category_created'),
        ], 201);
    }
}
