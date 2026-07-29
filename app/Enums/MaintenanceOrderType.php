<?php

namespace App\Enums;

enum MaintenanceOrderType: string
{
    case Preventive = 'preventive';
    case Corrective = 'corrective';

    public function label(): string
    {
        return match ($this) {
            self::Preventive => 'Preventivo',
            self::Corrective => 'Correctivo',
        };
    }
}
