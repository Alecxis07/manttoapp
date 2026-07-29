<?php

namespace App\DTOs;

use App\Enums\UserStatus;

readonly class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public UserStatus $status,
        public string $role,
        public ?string $password = null,
    ) {}
}
