<?php

namespace App\Actions\Users;

use App\DTOs\UserData;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateUser
{
    public function handle(UserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::query()->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => $data->password,
                'status' => $data->status,
                'email_verified_at' => now(),
            ]);

            $user->syncRoles([$data->role]);
            $this->createPersonalTeam($user);

            return $user->fresh(['roles']);
        });
    }

    protected function createPersonalTeam(User $user): void
    {
        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);

        $user->forceFill([
            'current_team_id' => $team->id,
        ])->save();
    }
}
