<?php

namespace App\Actions\Users;

use App\DTOs\UserData;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateUser
{
    public function handle(User $user, UserData $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor): User {
            if ($actor->id === $user->id) {
                if ($data->status === UserStatus::Inactive) {
                    throw ValidationException::withMessages([
                        'status' => __('No puedes desactivar tu propia cuenta.'),
                    ]);
                }

                if ($data->role !== $user->getRoleNames()->first()) {
                    throw ValidationException::withMessages([
                        'role' => __('No puedes cambiar tu propio rol.'),
                    ]);
                }
            }

            $attributes = [
                'name' => $data->name,
                'email' => $data->email,
                'status' => $data->status,
            ];

            if ($data->password !== null && $data->password !== '') {
                $attributes['password'] = $data->password;
            }

            $user->update($attributes);
            $user->syncRoles([$data->role]);

            return $user->fresh(['roles']);
        });
    }
}
