<?php

namespace App\Support;

use App\Enums\StaffRole;

final class AdminStaff
{
    public function __construct(
        public string $name,
        public string $email,
        public StaffRole $role,
    ) {}

    public static function fromSession(): self
    {
        $role = StaffRole::tryFrom((string) session('admin.role', StaffRole::SuperAdmin->value))
            ?? StaffRole::SuperAdmin;

        return new self(
            name: (string) session('admin.name', 'Ayşe Yılmaz'),
            email: (string) session('admin.email', 'ayse.yilmaz@nova.store'),
            role: $role,
        );
    }
}
