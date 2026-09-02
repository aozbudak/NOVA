<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(AdminStore $store): View
    {
        return view('admin.notifications.index', [
            'notifications' => $store->notifications(),
        ]);
    }

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
