<?php

namespace App\Providers;

use App\Support\AdminNavigation;
use App\Support\AdminStaff;
use App\Support\AdminStore;
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
        $this->app->bind(AdminStaff::class, fn (): AdminStaff => AdminStaff::fromSession());
        $this->app->bind(AdminNavigation::class, fn (): AdminNavigation => new AdminNavigation(
            $this->app->make(AdminStaff::class),
        ));
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
                'navItems' => app(Catalog::class)->navigation(),
                'customer' => session('storefront.customer'),
                'locales' => config('app.available_locales'),
                'currentLocale' => app()->getLocale(),
            ]);
        });

        View::composer(['layouts.admin', 'admin.*', 'components.admin.*'], function ($view): void {
            if (request()->routeIs('admin.login', 'admin.login.store')) {
                return;
            }

            $navigation = app(AdminNavigation::class);
            $staff = $navigation->staff();

            $view->with([
                'staff' => $staff,
                'navSections' => $navigation->sections(),
                'homeRoute' => $navigation->homeRoute(),
                'breadcrumbs' => $navigation->breadcrumbs(request()->route()?->getName()),
                'notifications' => app(AdminStore::class)->notifications(),
            ]);
        });
    }
}
