<?php

namespace App\DTOs;

use App\Enums\ServiceCatalogType;

/**
 * RN-CAT-002: basePrice is referential. Callers of orders/quotations must
 * snapshot the chosen unit price onto line items rather than binding to this value.
 */
readonly class ServiceCatalogData
{
    public function __construct(
        public string $code,
        public string $description,
        public int $serviceCategoryId,
        public ServiceCatalogType $type,
        public string $basePrice,
        public string $unitOfMeasure,
        public ?int $estimatedMinutes,
        public bool $isActive,
    ) {}
}
