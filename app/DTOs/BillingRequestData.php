<?php

namespace App\DTOs;

readonly class BillingRequestData
{
    public function __construct(
        public ?int $maintenanceOrderId,
        public ?int $quotationId,
        public ?int $customerFiscalProfileId,
        public ?string $paymentMethodCode,
        public ?string $paymentFormCode,
        public ?string $notes,
    ) {}
}
