<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'superadmin';
    case Admin = 'admin';

    public function label(): string
    {
        return match($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::SuperAdmin => 'danger',
            self::Admin => 'warning',
        };
    }
}
