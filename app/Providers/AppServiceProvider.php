<?php

namespace App\Providers;

use App\Support\Cart;
use App\Support\Catalog;
use App\Support\Wishlist;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.storefront', 'storefront.*'], function ($view): void {
            $view->with([
                'cartCount' => app(Cart::class)->count(),
                'wishlistIds' => app(Wishlist::class)->ids(),
                'searchIndex' => app(Catalog::class)->searchIndex(),
                'customer' => session('storefront.customer'),
            ]);
        });
    }
}
