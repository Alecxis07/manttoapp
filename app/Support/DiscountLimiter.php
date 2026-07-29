<?php

namespace App\Support;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

class DiscountLimiter
{
    public const ADMIN_MAX_PERCENT = null;

    public const ADMINISTRATIVO_MAX_PERCENT = 15.0;

    public const TECNICO_MAX_PERCENT = 0.0;

    public function maxPercentFor(User $user): ?float
    {
        if ($user->hasRole(RolesAndPermissionsSeeder::ROLE_ADMIN)) {
            return self::ADMIN_MAX_PERCENT;
        }

        if ($user->hasRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO)) {
            return self::ADMINISTRATIVO_MAX_PERCENT;
        }

        if ($user->hasRole(RolesAndPermissionsSeeder::ROLE_TECNICO)) {
            return self::TECNICO_MAX_PERCENT;
        }

        return 0.0;
    }

    public function allows(User $user, float|int|string $percent): bool
    {
        $percent = (float) $percent;
        $max = $this->maxPercentFor($user);

        if ($max === null) {
            return true;
        }

        return $percent <= $max + 0.00001;
    }

    /**
     * Discount percent relative to subtotal (0 when subtotal is 0).
     */
    public function percentOf(string $subtotal, string $discountTotal): float
    {
        if (bccomp($subtotal, '0', 2) !== 1) {
            return 0.0;
        }

        return (float) bcmul(bcdiv($discountTotal, $subtotal, 6), '100', 4);
    }
}
