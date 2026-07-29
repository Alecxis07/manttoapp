<?php

namespace Tests\Unit\Enums;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use PHPUnit\Framework\TestCase;

class CustomerEnumsTest extends TestCase
{
    public function test_customer_status_cases(): void
    {
        $this->assertSame('active', CustomerStatus::Active->value);
        $this->assertSame('inactive', CustomerStatus::Inactive->value);
        $this->assertTrue(CustomerStatus::Active->isActive());
        $this->assertFalse(CustomerStatus::Inactive->isActive());
        $this->assertSame('Activo', CustomerStatus::Active->label());
    }

    public function test_customer_type_cases(): void
    {
        $this->assertSame('individual', CustomerType::Individual->value);
        $this->assertSame('company', CustomerType::Company->value);
        $this->assertSame('Persona física', CustomerType::Individual->label());
        $this->assertSame('Persona moral', CustomerType::Company->label());
    }
}
