<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.payments.index', [
            'payments' => $store->payments(),
        ]);
    }
}
