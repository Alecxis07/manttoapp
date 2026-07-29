<?php

namespace Tests\Feature\Seeders;

use App\Models\Customer;
use App\Models\PartCatalog;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeders_create_expected_master_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $expectedUsers = [
            'admin@manttoapp.test' => RolesAndPermissionsSeeder::ROLE_ADMIN,
            'administrativo@manttoapp.test' => RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
            'tecnico@manttoapp.test' => RolesAndPermissionsSeeder::ROLE_TECNICO,
            'consulta@manttoapp.test' => RolesAndPermissionsSeeder::ROLE_CONSULTA,
        ];

        foreach ($expectedUsers as $email => $role) {
            $user = User::query()->where('email', $email)->first();

            $this->assertNotNull($user, "Expected user {$email} to exist.");
            $this->assertTrue($user->hasRole($role), "User {$email} should have role {$role}.");
            $this->assertTrue($user->ownedTeams()->where('personal_team', true)->exists(), "User {$email} should have a personal team.");
        }

        $this->assertGreaterThanOrEqual(50, Customer::query()->count());
        $this->assertGreaterThanOrEqual(100, Vehicle::query()->count());
        $this->assertGreaterThanOrEqual(8, ServiceCategory::query()->count());
        $this->assertGreaterThanOrEqual(50, ServiceCatalog::query()->count());
        $this->assertGreaterThanOrEqual(100, PartCatalog::query()->count());

        $this->assertFalse(Vehicle::query()->whereDoesntHave('customer')->exists());
        $this->assertFalse(Vehicle::query()->whereDoesntHave('vehicleType')->exists());

        Customer::query()->withCount([
            'fiscalProfiles as default_fiscal_profiles_count' => fn ($query) => $query->where('is_default', true),
        ])->each(function (Customer $customer): void {
            $this->assertGreaterThanOrEqual(
                1,
                $customer->default_fiscal_profiles_count,
                "Customer {$customer->id} should have a default fiscal profile."
            );
        });
    }
}
