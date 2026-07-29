<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

class TotalsCalculator
{
    public const DEFAULT_IVA_RATE = '16.00';

    public const IVA_SETTING_KEY = 'tax.iva_rate';

    /**
     * @param  Collection<int, array{quantity: float|int|string, unit_price: float|int|string, discount?: float|int|string}>|iterable<int, array{quantity: float|int|string, unit_price: float|int|string, discount?: float|int|string}>  $lines
     * @return array{subtotal: string, discount_total: string, tax_total: string, total: string, tax_rate: string}
     */
    public function calculate(iterable $lines, ?string $taxRate = null): array
    {
        $taxRate = $this->normalizeMoney($taxRate ?? $this->resolveTaxRate());

        $subtotal = '0.00';
        $discountTotal = '0.00';

        foreach ($lines as $line) {
            $quantity = $this->normalizeMoney($line['quantity'] ?? 0);
            $unitPrice = $this->normalizeMoney($line['unit_price'] ?? 0);
            $lineDiscount = $this->normalizeMoney($line['discount'] ?? 0);

            $lineGross = bcmul($quantity, $unitPrice, 2);
            $subtotal = bcadd($subtotal, $lineGross, 2);
            $discountTotal = bcadd($discountTotal, $lineDiscount, 2);
        }

        $taxable = bcsub($subtotal, $discountTotal, 2);

        if (bccomp($taxable, '0', 2) === -1) {
            $taxable = '0.00';
        }

        $taxTotal = bcmul($taxable, bcdiv($taxRate, '100', 6), 2);
        $total = bcadd($taxable, $taxTotal, 2);

        return [
            'subtotal' => $this->normalizeMoney($subtotal),
            'discount_total' => $this->normalizeMoney($discountTotal),
            'tax_total' => $this->normalizeMoney($taxTotal),
            'total' => $this->normalizeMoney($total),
            'tax_rate' => $taxRate,
        ];
    }

    public function resolveTaxRate(): string
    {
        $value = Setting::query()->where('key', self::IVA_SETTING_KEY)->value('value');

        if ($value === null || $value === '') {
            return self::DEFAULT_IVA_RATE;
        }

        return $this->normalizeMoney($value);
    }

    private function normalizeMoney(float|int|string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
