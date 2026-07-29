<?php

namespace App\DTOs;

use App\Enums\PartCatalogType;

/**
 * RN-CAT-002: basePrice is referential. Documents snapshot unit price on line items.
 */
readonly class PartCatalogData
{
    public function __construct(
        public string $code,
        public string $description,
        public PartCatalogType $type,
        public ?int $serviceCategoryId,
        public string $basePrice,
        public string $unitOfMeasure,
        public bool $isActive,
    ) {}
}
