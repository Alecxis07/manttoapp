<?php

namespace App\DTOs;

readonly class ServiceCategoryData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public bool $isActive,
    ) {}
}
