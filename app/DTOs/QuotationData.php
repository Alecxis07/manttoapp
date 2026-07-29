<?php

namespace App\DTOs;

readonly class QuotationData
{
    /**
     * @param  list<array{
     *     item_type: string,
     *     service_catalog_id?: int|null,
     *     part_catalog_id?: int|null,
     *     code?: string|null,
     *     description: string,
     *     quantity: float|int|string,
     *     unit_price: float|int|string,
     *     discount?: float|int|string,
     *     notes?: string|null
     * }>  $items
     */
    public function __construct(
        public int $customerId,
        public int $vehicleId,
        public ?string $validUntil,
        public ?string $commercialTerms,
        public array $items,
    ) {}
}
