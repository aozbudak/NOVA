<?php

use App\Http\Controllers\Api\AdminSearchController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CashController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\ReturnRequestController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\VariantController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureAdminPageAccess;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->name('api.')->group(function (): void {
    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/{product}', [CatalogController::class, 'show'])->name('catalog.show');
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/discounts', [DiscountController::class, 'index'])->name('discounts.index');
    Route::get('/search', SearchController::class)->name('search');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{key}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{key}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');

    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/return-requests', [ReturnRequestController::class, 'index'])->name('return-requests.index');
    Route::post('/return-requests', [ReturnRequestController::class, 'store'])->name('return-requests.store');
    Route::get('/return-requests/{returnRequest}', [ReturnRequestController::class, 'show'])->name('return-requests.show');
});

Route::middleware(['web', EnsureAdminAuthenticated::class, EnsureAdminPageAccess::class])->name('api.')->group(function (): void {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::post('/products/{product}/deactivate', [ProductController::class, 'deactivate'])->name('products.deactivate');

    Route::get('/variants', VariantController::class)->name('variants.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/header', [CategoryController::class, 'attachHeader'])->name('categories.header.store');
    Route::delete('/categories/{category}/header', [CategoryController::class, 'detachHeader'])->name('categories.header.destroy');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::post('/discounts', [DiscountController::class, 'store'])->name('discounts.store');
    Route::put('/discounts/{discount}', [DiscountController::class, 'update'])->name('discounts.update');
    Route::delete('/discounts/{discount}', [DiscountController::class, 'destroy'])->name('discounts.destroy');
    Route::get('/pos/items', [PosController::class, 'items'])->name('pos.items');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');

    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('/returns', [ReturnController::class, 'store'])->name('returns.store');
    Route::get('/returns/{return}', [ReturnController::class, 'show'])->name('returns.show');

    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');

    Route::get('/cash', [CashController::class, 'show'])->name('cash.show');
    Route::get('/cash/movements', [CashController::class, 'movements'])->name('cash.movements');
    Route::post('/cash/open', [CashController::class, 'open'])->name('cash.open');
    Route::post('/cash/close', [CashController::class, 'close'])->name('cash.close');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');

    Route::get('/payments', PaymentController::class)->name('payments.index');
    Route::get('/admin/search', AdminSearchController::class)->name('admin.search');
});
