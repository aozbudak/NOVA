<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function read(string $notification, AdminStore $store): RedirectResponse
    {
        $store->markNotificationRead($notification);

        return back();
    }

    public function readAll(AdminStore $store): RedirectResponse
    {
        $store->markAllNotificationsRead();

        return back();
    }
}
