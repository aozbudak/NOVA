<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
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
            'movements' => $store->cashMovements(),
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

    public function storeOpening(): RedirectResponse
    {
        return redirect()->route('admin.cash.index');
    }

    public function storeClosing(): RedirectResponse
    {
        return redirect()->route('admin.cash.index');
    }
}
