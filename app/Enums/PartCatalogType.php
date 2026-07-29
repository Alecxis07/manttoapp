<?php

namespace App\Enums;

enum PartCatalogType: string
{
    case Part = 'part';
    case Labor = 'labor';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Part => 'Refacción',
            self::Labor => 'Mano de obra',
            self::Other => 'Otro',
        };
    }
}
