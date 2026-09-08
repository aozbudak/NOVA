<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\SaleReturn;
use App\Support\CustomerAccount;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReturnRequestController extends Controller
{
    public function index(CustomerAccount $account): View|RedirectResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return redirect()->route('login');
        }

        return view('storefront.account.returns', [
            'returns' => $account->returnSummaries($customer),
            'returnableOrders' => $account->returnableOrderOptions($customer),
            'reasons' => $account->reasonOptions(),
        ]);
    }

    public function show(string $returnRequest, CustomerAccount $account): View|RedirectResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return redirect()->route('login');
        }

        return view('storefront.account.return', [
            'returnRequest' => $account->detailReturn($account->returnRequest($customer, $returnRequest)),
        ]);
    }

    public function store(Request $request, CustomerAccount $account, DatabaseRecords $records): RedirectResponse
    {
        $customer = $account->customer();

        if ($customer === null) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'order_id' => ['required', 'string', 'max:255'],
            'reason' => ['required', 'string', Rule::in(SaleReturn::customerReasons())],
            'notes' => ['nullable', 'string', 'max:1000', 'required_if:reason,other'],
        ]);

        $order = $account->order($customer, $validated['order_id']);
        $notes = filled($validated['notes'] ?? null) ? trim((string) $validated['notes']) : null;
        $return = $records->requestReturn($customer, $order, $validated['reason'], $notes);

        return redirect()
            ->route('account.returns.show', $return->return_number)
            ->with('status', __('storefront.account.return_submitted'));
    }
}
