<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AdminStaff;
use App\Support\AdminStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function __invoke(Request $request, AdminStore $store, AdminStaff $staff): JsonResponse
    {
        $query = $request->string('q')->toString();

        return response()->json([
            'query' => $query,
            'groups' => $store->search($query, $staff),
        ]);
    }
}
