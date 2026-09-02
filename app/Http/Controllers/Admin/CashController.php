<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminList;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.cash.index', [
            'register' => $store->cashRegister(),
        ]);
    }

    public function movements(AdminStore $store): View
    {
        return view('admin.cash.movements', [
            'movements' => AdminList::apply(collect($store->cashMovements()), ['date', 'amount']),
        ]);
    }

    public function open(AdminStore $store): View
    {
        return view('admin.cash.open', [
            'register' => $store->cashRegister(),
        ]);
    }

    public function close(AdminStore $store): View
    {
        return view('admin.cash.close', [
            'register' => $store->cashRegister(),
        ]);
    }

    public function storeOpening(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $validated = $request->validate([
            'opening' => ['required', 'numeric', 'min:0'],
            'date' => ['nullable', 'date'],
            'user' => ['nullable', 'string', 'max:255'],
        ]);

        $records->openRegister((float) $validated['opening']);

        return redirect()
            ->route('admin.cash.index')
            ->with('status', __('admin.toast.register_opened'));
    }

    public function storeClosing(Request $request, DatabaseRecords $records): RedirectResponse
    {
        $validated = $request->validate([
            'actual' => ['required', 'numeric', 'min:0'],
        ]);

        $records->closeRegister((float) $validated['actual']);

        return redirect()
            ->route('admin.cash.index')
            ->with('status', __('admin.toast.register_closed'));
    }
}
