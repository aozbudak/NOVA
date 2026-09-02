<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Admin\PanelPageController;
use App\Http\Controllers\Storefront\AccountController;
use App\Http\Controllers\Storefront\AuthController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\CollectionController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\LocaleController;
use App\Http\Controllers\Storefront\PageController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\WishlistController;
use App\Http\Middleware\EnsureAdminPageAccess;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/locale/{locale}', [LocaleController::class, 'update'])
    ->whereIn('locale', array_keys(config('app.available_locales')))
    ->name('locale.update');

Route::get('/shop/{department}/{category?}', [CollectionController::class, 'show'])
    ->name('shop.show');

Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show');

Route::get('/search', SearchController::class)->name('search');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');

Route::get('/cart/panel', [CartController::class, 'panel'])->name('cart.panel');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{key}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/confirmation', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');

Route::get('/account', [AccountController::class, 'show'])->name('account.show');
Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
Route::get('/account/settings', [AccountController::class, 'settings'])->name('account.settings');
Route::post('/account/logout', [AccountController::class, 'logout'])->name('account.logout');

Route::get('/pages/{page}', PageController::class)->name('pages.show');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::middleware(EnsureAdminPageAccess::class)->group(function (): void {
        Route::get('/products', PanelPageController::class)->name('products.index');
        Route::get('/categories', PanelPageController::class)->name('categories.index');
        Route::get('/brands', PanelPageController::class)->name('brands.index');
        Route::get('/variants', PanelPageController::class)->name('variants.index');
        Route::get('/inventory', PanelPageController::class)->name('inventory.index');
        Route::get('/barcode', PanelPageController::class)->name('barcode.index');
        Route::get('/pos', PanelPageController::class)->name('pos.index');
        Route::get('/sales', PanelPageController::class)->name('sales.index');
        Route::get('/returns', PanelPageController::class)->name('returns.index');
        Route::get('/exchanges', PanelPageController::class)->name('exchanges.index');
        Route::get('/customers', PanelPageController::class)->name('customers.index');
        Route::get('/suppliers', PanelPageController::class)->name('suppliers.index');
        Route::get('/cash', PanelPageController::class)->name('cash.index');
        Route::get('/income-expense', PanelPageController::class)->name('income-expense.index');
        Route::get('/payments', PanelPageController::class)->name('payments.index');
        Route::get('/reports/sales', PanelPageController::class)->name('reports.sales');
        Route::get('/reports/inventory', PanelPageController::class)->name('reports.inventory');
        Route::get('/reports/cash', PanelPageController::class)->name('reports.cash');
        Route::get('/users', PanelPageController::class)->name('users.index');
        Route::get('/roles', PanelPageController::class)->name('roles.index');
        Route::get('/audit', PanelPageController::class)->name('audit.index');
        Route::get('/settings', PanelPageController::class)->name('settings.index');
        Route::get('/profile', PanelPageController::class)->name('profile.show');
    });
});
