<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminStaff;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(AdminStore $store, AdminStaff $staff): View
    {
        return view('admin.profile.show', [
            'profile' => $store->currentProfile($staff),
        ]);
    }

    public function update(Request $request, AdminStaff $staff, DatabaseRecords $records): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ]);

        $records->updateProfile($staff->email, $data);

        session([
            'admin.name' => $data['name'],
            'admin.email' => $data['email'],
            'admin.phone' => $data['phone'] ?? '',
        ]);

        return redirect()
            ->route('admin.profile.show')
            ->with('status', __('admin.toast.profile_updated'));
    }

    public function password(Request $request, AdminStaff $staff, DatabaseRecords $records): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $records->updatePassword($staff->email, $request->string('current_password')->toString(), $request->string('password')->toString())) {
            return back()->withErrors(['current_password' => __('auth.password')]);
        }

        return redirect()
            ->route('admin.profile.show')
            ->with('status', __('admin.toast.password_updated'));
    }
}
