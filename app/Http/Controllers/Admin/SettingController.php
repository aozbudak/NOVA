<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * @var list<string>
     */
    private const CATEGORIES = ['general', 'store', 'sales', 'inventory', 'notifications', 'security', 'system'];

    public function index(AdminStore $store, ?string $category = null): View
    {
        $category ??= 'general';

        abort_unless(in_array($category, self::CATEGORIES, true), 404);

        return view('admin.settings.index', [
            'category' => $category,
            'categories' => $store->settingCategories(),
            'settings' => $store->settings(),
        ]);
    }

    public function update(Request $request, string $category, AdminStore $store): RedirectResponse
    {
        abort_unless(in_array($category, self::CATEGORIES, true), 404);

        $data = $request->validate($this->rules($category));

        foreach ($this->booleans($category) as $key) {
            $data[$key] = $request->boolean($key);
        }

        $store->updateSettings($data);

        return redirect()
            ->route('admin.settings.index', $category)
            ->with('status', __('admin.toast.settings_saved'));
    }

    /**
     * @return list<string>
     */
    private function booleans(string $category): array
    {
        return match ($category) {
            'sales' => ['payment_cash', 'payment_card'],
            'inventory' => ['allow_negative_stock'],
            'notifications' => ['notify_low_stock', 'notify_sales', 'notify_returns'],
            'security' => ['login_protection'],
            default => [],
        };
    }

    /**
     * @return array<string, list<string>>
     */
    private function rules(string $category): array
    {
        return match ($category) {
            'general' => [
                'store_name' => ['required', 'string', 'max:255'],
                'store_email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
                'currency' => ['required', 'string', 'max:8'],
            ],
            'store' => [
                'store_info' => ['required', 'string', 'max:500'],
                'opening_hours' => ['required', 'string', 'max:255'],
                'default_language' => ['required', 'in:en,tr'],
            ],
            'sales' => [
                'default_discount' => ['required', 'integer', 'min:0', 'max:100'],
                'payment_cash' => ['sometimes', 'boolean'],
                'payment_card' => ['sometimes', 'boolean'],
                'receipt_footer' => ['required', 'string', 'max:255'],
            ],
            'inventory' => [
                'low_stock_threshold' => ['required', 'integer', 'min:0'],
                'allow_negative_stock' => ['sometimes', 'boolean'],
            ],
            'notifications' => [
                'notify_low_stock' => ['sometimes', 'boolean'],
                'notify_sales' => ['sometimes', 'boolean'],
                'notify_returns' => ['sometimes', 'boolean'],
            ],
            'security' => [
                'session_timeout' => ['required', 'integer', 'min:5', 'max:1440'],
                'password_min' => ['required', 'integer', 'min:8', 'max:32'],
                'login_protection' => ['sometimes', 'boolean'],
            ],
            'system' => [
                'timezone' => ['required', 'string', 'max:64'],
            ],
            default => [],
        };
    }
}
