<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Active = 'active';
    case InService = 'in_service';
    case Inactive = 'inactive';
    case Baja = 'baja';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::InService => 'En servicio',
            self::Inactive => 'Inactiva',
            self::Baja => 'Baja',
        };
    }

    public function isOperationallyActive(): bool
    {
        return $this === self::Active || $this === self::InService;
    }

    /**
     * @return list<self>
     */
    public static function activeStatuses(): array
    {
        return [self::Active, self::InService];
    }
}
