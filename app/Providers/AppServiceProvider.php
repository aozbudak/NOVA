<?php

namespace App\Providers;

use App\Support\AdminNavigation;
use App\Support\AdminStaff;
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
        $this->app->scoped(AdminStaff::class, fn (): AdminStaff => AdminStaff::fromSession());
        $this->app->scoped(AdminNavigation::class, fn (): AdminNavigation => new AdminNavigation(
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
            $navigation = app(AdminNavigation::class);
            $staff = $navigation->staff();

            $view->with([
                'staff' => $staff,
                'navSections' => $navigation->sections(),
                'homeRoute' => $navigation->homeRoute(),
                'breadcrumbs' => $navigation->breadcrumbs(request()->route()?->getName()),
                'notifications' => [
                    [
                        'title' => __('admin.notifications.low_stock'),
                        'body' => 'Merino Crew Knit · SKU NV-KN-044',
                        'time' => '12m',
                        'unread' => true,
                    ],
                    [
                        'title' => __('admin.notifications.return_opened'),
                        'body' => 'NV-10461 · Elif Kaya',
                        'time' => '38m',
                        'unread' => true,
                    ],
                    [
                        'title' => __('admin.notifications.till_closed'),
                        'body' => __('admin.notifications.till_closed_body'),
                        'time' => '2h',
                        'unread' => false,
                    ],
                ],
            ]);
        });
    }
}
