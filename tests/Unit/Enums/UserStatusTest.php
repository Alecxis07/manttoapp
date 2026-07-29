<?php

namespace Tests\Unit\Enums;

use App\Enums\UserStatus;
use PHPUnit\Framework\TestCase;

class UserStatusTest extends TestCase
{
    public function test_active_and_inactive_cases_exist(): void
    {
        $this->assertSame('active', UserStatus::Active->value);
        $this->assertSame('inactive', UserStatus::Inactive->value);
        $this->assertTrue(UserStatus::Active->isActive());
        $this->assertFalse(UserStatus::Inactive->isActive());
    }
}
