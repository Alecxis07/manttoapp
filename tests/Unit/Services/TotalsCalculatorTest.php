<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\TotalsCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TotalsCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_totals_with_default_iva_rate(): void
    {
        $calculator = new TotalsCalculator;

        $result = $calculator->calculate([
            ['quantity' => 2, 'unit_price' => '100.00', 'discount' => '0'],
            ['quantity' => 1, 'unit_price' => '50.00', 'discount' => '10.00'],
        ]);

        $this->assertSame('250.00', $result['subtotal']);
        $this->assertSame('10.00', $result['discount_total']);
        $this->assertSame('16.00', $result['tax_rate']);
        $this->assertSame('38.40', $result['tax_total']);
        $this->assertSame('278.40', $result['total']);
    }

    public function test_reads_iva_rate_from_settings(): void
    {
        Setting::query()->create([
            'key' => TotalsCalculator::IVA_SETTING_KEY,
            'value' => '8.00',
            'type' => 'decimal',
            'group' => 'tax',
        ]);

        $calculator = new TotalsCalculator;

        $result = $calculator->calculate([
            ['quantity' => 1, 'unit_price' => '100.00'],
        ]);

        $this->assertSame('8.00', $result['tax_rate']);
        $this->assertSame('8.00', $result['tax_total']);
        $this->assertSame('108.00', $result['total']);
    }

    public function test_explicit_tax_rate_overrides_settings(): void
    {
        Setting::query()->create([
            'key' => TotalsCalculator::IVA_SETTING_KEY,
            'value' => '16.00',
            'type' => 'decimal',
            'group' => 'tax',
        ]);

        $result = (new TotalsCalculator)->calculate([
            ['quantity' => 1, 'unit_price' => '100.00'],
        ], '0');

        $this->assertSame('0.00', $result['tax_rate']);
        $this->assertSame('0.00', $result['tax_total']);
        $this->assertSame('100.00', $result['total']);
    }
}
