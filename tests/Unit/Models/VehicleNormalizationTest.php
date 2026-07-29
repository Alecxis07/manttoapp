<?php

namespace Tests\Unit\Models;

use App\Models\Vehicle;
use PHPUnit\Framework\TestCase;

class VehicleNormalizationTest extends TestCase
{
    public function test_normalize_plate_strips_spaces_hyphens_and_uppercases(): void
    {
        $this->assertSame('ABC1234', Vehicle::normalizePlate('abc-12-34'));
        $this->assertSame('ABC1234', Vehicle::normalizePlate(' ABC 12 34 '));
        $this->assertSame('ABC1234', Vehicle::normalizePlate('abc_12_34'));
    }

    public function test_normalize_vin_uppercases_and_strips_spaces(): void
    {
        $this->assertSame('1HGBH41JXMN109186', Vehicle::normalizeVin('1hgbh41jxmn109186'));
        $this->assertSame('1HGBH41JXMN109186', Vehicle::normalizeVin(' 1HGBH41JXMN 109186 '));
        $this->assertNull(Vehicle::normalizeVin(null));
        $this->assertNull(Vehicle::normalizeVin('   '));
    }
}
