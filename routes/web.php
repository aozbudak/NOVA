<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CashController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExchangeController;
use App\Http\Controllers\Admin\IncomeExpenseController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VariantController;
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
use App\Http\Middleware\EnsureAdminAuthenticated;
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
    Route::get('/login', [AdminAuthController::class, 'show'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

    Route::middleware(EnsureAdminAuthenticated::class)->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('/logout', LogoutController::class)->name('logout');

        Route::middleware(EnsureAdminPageAccess::class)->group(function (): void {
            Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
            Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
            Route::post('/products/{product}/deactivate', [AdminProductController::class, 'deactivate'])->name('products.deactivate');
            Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
            Route::get('/categories', CategoryController::class)->name('categories.index');
            Route::get('/brands', BrandController::class)->name('brands.index');
            Route::get('/variants', VariantController::class)->name('variants.index');
            Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
            Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
            Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
            Route::get('/barcode', BarcodeController::class)->name('barcode.index');
            Route::get('/pos', PosController::class)->name('pos.index');
            Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
            Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
            Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
            Route::get('/returns/create', [ReturnController::class, 'create'])->name('returns.create');
            Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
            Route::get('/returns/{return}', [ReturnController::class, 'show'])->name('returns.show');
            Route::get('/exchanges', [ExchangeController::class, 'index'])->name('exchanges.index');
            Route::get('/exchanges/{exchange}', [ExchangeController::class, 'show'])->name('exchanges.show');
            Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
            Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
            Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
            Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
            Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
            Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
            Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
            Route::get('/cash', [CashController::class, 'index'])->name('cash.index');
            Route::get('/cash/movements', [CashController::class, 'movements'])->name('cash.movements');
            Route::get('/cash/open', [CashController::class, 'open'])->name('cash.open');
            Route::post('/cash/open', [CashController::class, 'storeOpening'])->name('cash.open.store');
            Route::get('/cash/close', [CashController::class, 'close'])->name('cash.close');
            Route::post('/cash/close', [CashController::class, 'storeClosing'])->name('cash.close.store');
            Route::get('/income-expense', [IncomeExpenseController::class, 'index'])->name('income-expense.index');
            Route::get('/income-expense/create', [IncomeExpenseController::class, 'create'])->name('income-expense.create');
            Route::post('/income-expense', [IncomeExpenseController::class, 'store'])->name('income-expense.store');
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
            Route::get('/reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
            Route::get('/search', AdminSearchController::class)->name('search');
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
            Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
            Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
            Route::get('/audit/{audit}', [AuditController::class, 'show'])->name('audit.show');
            Route::get('/settings/{category?}', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/settings/{category}', [SettingController::class, 'update'])->name('settings.update');
            Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
        });
    });
});
