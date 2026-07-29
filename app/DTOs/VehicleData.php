<?php

namespace App\DTOs;

use App\Enums\VehicleStatus;

readonly class VehicleData
{
    public function __construct(
        public int $customerId,
        public int $vehicleTypeId,
        public string $licensePlate,
        public ?string $vin,
        public ?string $economicNumber,
        public string $brand,
        public string $model,
        public int $year,
        public ?string $engineType,
        public int $currentMileage,
        public VehicleStatus $status,
        public ?string $statusNotes = null,
    ) {}
}
