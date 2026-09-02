<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AdminNavigation $navigation): View|RedirectResponse
    {
        if (! $navigation->staff()->role->can('dashboard')) {
            return redirect()->route($navigation->homeRoute());
        }

        return view('admin.dashboard', [
            'metrics' => [
                [
                    'label' => __('admin.dashboard.metrics.today_sales'),
                    'value' => '€12.480',
                    'hint' => __('admin.dashboard.metrics.today_sales_hint'),
                ],
                [
                    'label' => __('admin.dashboard.metrics.open_tills'),
                    'value' => '3',
                    'hint' => __('admin.dashboard.metrics.open_tills_hint'),
                ],
                [
                    'label' => __('admin.dashboard.metrics.low_stock'),
                    'value' => '18',
                    'hint' => __('admin.dashboard.metrics.low_stock_hint'),
                ],
                [
                    'label' => __('admin.dashboard.metrics.pending_returns'),
                    'value' => '6',
                    'hint' => __('admin.dashboard.metrics.pending_returns_hint'),
                ],
            ],
            'sales' => [
                ['ref' => 'NV-10482', 'customer' => 'Elif Kaya', 'channel' => 'POS', 'total' => '€186,00', 'time' => '09:14'],
                ['ref' => 'NV-10481', 'customer' => 'Mert Aydın', 'channel' => 'POS', 'total' => '€92,50', 'time' => '08:51'],
                ['ref' => 'NV-10480', 'customer' => 'Selin Arslan', 'channel' => 'POS', 'total' => '€244,00', 'time' => '08:22'],
                ['ref' => 'NV-10479', 'customer' => 'Can Demir', 'channel' => 'POS', 'total' => '€61,00', 'time' => '08:04'],
            ],
            'stock' => [
                ['sku' => 'NV-CT-012', 'name' => 'Structured Wool Coat', 'qty' => 2, 'location' => 'A-12'],
                ['sku' => 'NV-KN-044', 'name' => 'Merino Crew Knit', 'qty' => 4, 'location' => 'B-03'],
                ['sku' => 'NV-TR-021', 'name' => 'Tailored Trouser', 'qty' => 3, 'location' => 'C-18'],
            ],
        ]);
    }
}
