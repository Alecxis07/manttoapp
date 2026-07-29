<?php

namespace App\Actions\Users;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeactivateUser
{
    public function handle(User $user, User $actor): User
    {
        if ($actor->id === $user->id) {
            throw ValidationException::withMessages([
                'user' => __('No puedes desactivar tu propia cuenta.'),
            ]);
        }

        $user->update([
            'status' => UserStatus::Inactive,
        ]);

        return $user->fresh(['roles']);
    }
}
