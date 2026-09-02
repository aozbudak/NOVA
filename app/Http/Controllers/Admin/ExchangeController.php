<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\View\View;

class ExchangeController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.exchanges.index', [
            'exchanges' => $store->exchanges(),
        ]);
    }

    public function show(string $exchange, AdminStore $store): View
    {
        $record = $store->exchange($exchange);

        abort_if($record === null, 404);

        return view('admin.exchanges.show', [
            'exchange' => $record,
        ]);
    }
}
