<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StaffRole;
use App\Http\Controllers\Controller;
use App\Support\AdminStore;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(AdminStore $store): View
    {
        $users = $store->users();

        return view('admin.roles.index', [
            'roles' => $store->roles()
                ->map(fn (array $role): array => [
                    'key' => $role['id'],
                    'label' => $role['name'],
                    'builtin' => $role['builtin'],
                    'users' => $users->where('role', $role['id'])->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values(),
                ])
                ->all(),
        ]);
    }

    public function show(string $role, AdminStore $store): View
    {
        $record = $store->role($role);

        abort_if($record === null, 404);

        $builtin = StaffRole::tryFrom($record['id']);

        return view('admin.roles.show', [
            'role' => $record,
            'operations' => StaffRole::operations(),
            'groups' => StaffRole::abilityGroups(),
            'actions' => StaffRole::abilityActions(),
            'matrix' => $builtin?->matrix(),
            'users' => $store->users()
                ->where('role', $record['id'])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values(),
        ]);
    }
}
