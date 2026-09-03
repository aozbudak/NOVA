<?php

namespace App\Support;

use App\Enums\StaffRole;

final class AdminStaff
{
    /**
     * @param  list<string>|null  $customAbilities
     */
    public function __construct(
        public string $name,
        public string $email,
        public StaffRole $role,
        public string $phone = '',
        public ?array $customAbilities = null,
    ) {}

    public static function fromSession(): self
    {
        $raw = (string) session('admin.role', StaffRole::SuperAdmin->value);
        $role = StaffRole::tryFrom($raw);
        $customAbilities = null;

        if ($role === null) {
            $customAbilities = self::sessionAbilities($raw);
            $role = StaffRole::Cashier;
        }

        return new self(
            name: (string) session('admin.name', 'Ayşe Yılmaz'),
            email: (string) session('admin.email', 'ayse.yilmaz@nova.store'),
            role: $role,
            phone: (string) session('admin.phone', '0532 441 00 11'),
            customAbilities: $customAbilities,
        );
    }

    public function can(string $permission): bool
    {
        if ($this->customAbilities !== null) {
            return $permission === 'profile' || in_array($permission, $this->customAbilities, true);
        }

        return $this->role->can($permission);
    }

    /**
     * @return list<string>
     */
    private static function sessionAbilities(string $roleId): array
    {
        $abilities = session('admin.abilities');

        if (is_array($abilities)) {
            return array_values(array_filter($abilities, fn (mixed $ability): bool => is_string($ability)));
        }

        $roles = session('admin.roles', []);
        $record = is_array($roles[$roleId] ?? null) ? $roles[$roleId] : [];

        return array_values(array_filter(
            $record['abilities'] ?? [],
            fn (mixed $ability): bool => is_string($ability),
        ));
    }
}
