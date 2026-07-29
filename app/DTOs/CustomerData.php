<?php

namespace App\DTOs;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;

readonly class CustomerData
{
    /**
     * @param  list<array{
     *     id?: int|null,
     *     legal_name: string,
     *     rfc: string,
     *     tax_regime_code: string,
     *     cfdi_use_code: string,
     *     postal_code: string,
     *     email?: string|null,
     *     is_default: bool
     * }>  $fiscalProfiles
     */
    public function __construct(
        public CustomerType $type,
        public string $name,
        public ?string $tradeName,
        public ?string $phone,
        public ?string $email,
        public CustomerStatus $status,
        public array $fiscalProfiles = [],
    ) {}
}
