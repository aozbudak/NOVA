<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.categories.index', [
            'categories' => AdminList::apply($store->categoryRecords(), ['name', 'products', 'stock', 'status']),
        ]);
    }

    public function store(Request $request, AdminStore $store): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $exists = $store->categoryRecords()->contains(
            fn (array $row): bool => strcasecmp($row['name'], $data['name']) === 0,
        );

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => __('validation.unique', ['attribute' => __('admin.categories.name')]),
            ]);
        }

        $store->createCategory($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', __('admin.toast.category_created'));
    }
}
