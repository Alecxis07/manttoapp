<?php

namespace App\Enums;

enum ExpedienteEventType: string
{
    case Order = 'order';
    case Quotation = 'quotation';
    case Billing = 'billing';
    case Evidence = 'evidence';

    public function label(): string
    {
        return match ($this) {
            self::Order => 'Orden',
            self::Quotation => 'Cotización',
            self::Billing => 'Facturación',
            self::Evidence => 'Evidencia',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn (self $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->all();
    }
}
