<?php

namespace App\Enums;

enum QuotationItemType: string
{
    case Service = 'service';
    case Part = 'part';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Servicio',
            self::Part => 'Refacción',
        };
    }
}
