<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.roles.index');
    }

    public function create(AdminStore $store): View
    {
        return view('admin.users.form', $this->formData($store));
    }

    public function store(Request $request, AdminStore $store): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'string', 'min:8'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', Rule::in(StaffRole::operations())],
        ]);

        $role = $store->resolveRole($data['role'], $data['abilities'] ?? []);

        $store->createUser([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'role' => $role['id'],
            'status' => $data['status'],
            'abilities' => $data['abilities'] ?? [],
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('admin.toast.user_created'));
    }

    public function edit(string $user, AdminStore $store): View
    {
        $record = $store->user($user);

        abort_if($record === null, 404);

        return view('admin.users.form', $this->formData($store, $record));
    }

    public function update(Request $request, string $user, AdminStore $store): RedirectResponse
    {
        abort_if($store->user($user) === null, 404);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', Rule::in(StaffRole::operations())],
        ]);

        $role = $store->resolveRole($data['role'], $data['abilities'] ?? []);

        $store->updateUser($user, [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'role' => $role['id'],
            'status' => $data['status'],
            'abilities' => $data['abilities'] ?? [],
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', __('admin.toast.user_updated'));
    }

    /**
     * @param  array<string, mixed>|null  $user
     * @return array{user: array<string, mixed>|null, roleName: string, roles: list<array{id: string, name: string}>, operations: list<string>, roleAbilities: array<string, list<string>>}
     */
    private function formData(AdminStore $store, ?array $user = null): array
    {
        $roles = $store->roles();
        $current = $user === null ? null : $store->role((string) $user['role']);

        return [
            'user' => $user,
            'roleName' => $current['name'] ?? StaffRole::SuperAdmin->label(),
            'roles' => $roles
                ->map(fn (array $role): array => [
                    'id' => $role['id'],
                    'name' => $role['name'],
                ])
                ->all(),
            'operations' => StaffRole::operations(),
            'roleAbilities' => $roles
                ->mapWithKeys(fn (array $role): array => [$role['name'] => $role['abilities']])
                ->all(),
        ];
    }
}
