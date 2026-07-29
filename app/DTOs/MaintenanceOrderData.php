<?php

namespace App\DTOs;

use App\Enums\MaintenanceOrderType;
use Carbon\CarbonInterface;

readonly class MaintenanceOrderData
{
    public function __construct(
        public int $customerId,
        public int $vehicleId,
        public MaintenanceOrderType $type,
        public string $reason,
        public int $mileage,
        public CarbonInterface $receivedAt,
        public ?int $assignedUserId = null,
        public ?string $technicalNotes = null,
        public ?float $clientSubtotal = null,
        public ?float $clientDiscountTotal = null,
        public ?float $clientTaxTotal = null,
        public ?float $clientTotal = null,
    ) {}
}
