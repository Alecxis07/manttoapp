<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * @var list<array{name: string, email: string, role: string}>
     */
    private const FIXED_USERS = [
        [
            'name' => 'Admin Mantto',
            'email' => 'admin@manttoapp.test',
            'role' => RolesAndPermissionsSeeder::ROLE_ADMIN,
        ],
        [
            'name' => 'Administrativo Mantto',
            'email' => 'administrativo@manttoapp.test',
            'role' => RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
        ],
        [
            'name' => 'Técnico Mantto',
            'email' => 'tecnico@manttoapp.test',
            'role' => RolesAndPermissionsSeeder::ROLE_TECNICO,
        ],
        [
            'name' => 'Consulta Mantto',
            'email' => 'consulta@manttoapp.test',
            'role' => RolesAndPermissionsSeeder::ROLE_CONSULTA,
        ],
    ];

    public function run(): void
    {
        foreach (self::FIXED_USERS as $fixedUser) {
            $user = User::query()->where('email', $fixedUser['email'])->first();

            if ($user === null) {
                $user = User::factory()->withPersonalTeam()->create([
                    'name' => $fixedUser['name'],
                    'email' => $fixedUser['email'],
                ]);
            }

            $user->syncRoles([$fixedUser['role']]);
        }

        $extraRoles = [
            RolesAndPermissionsSeeder::ROLE_TECNICO,
            RolesAndPermissionsSeeder::ROLE_CONSULTA,
        ];

        User::factory()
            ->count(6)
            ->withPersonalTeam()
            ->create()
            ->each(function (User $user) use ($extraRoles): void {
                $user->assignRole(fake()->randomElement($extraRoles));
            });
    }
}
