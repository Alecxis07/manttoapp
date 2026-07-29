<?php

namespace Tests\Feature\Vehicles;

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_quick_plate_search_is_format_insensitive(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create([
            'license_plate' => 'ABC-12-34',
        ]);

        $this->assertSame('ABC1234', $vehicle->license_plate_normalized);

        $response = $this->actingAs($admin)
            ->getJson(route('vehicles.search', ['plate' => 'abc 12-34', 'json' => 1]));

        $response->assertOk()
            ->assertJsonPath('data.0.id', $vehicle->id)
            ->assertJsonPath('data.0.license_plate', 'ABC-12-34');
    }

    public function test_single_match_redirects_to_detail(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $vehicle = Vehicle::factory()->create([
            'license_plate' => 'ONLY-99',
        ]);

        $this->actingAs($admin)
            ->get(route('vehicles.search', ['plate' => 'only99']))
            ->assertRedirect(route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]));
    }

    public function test_partial_plate_search_returns_matches(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        Vehicle::factory()->create(['license_plate' => 'XYZ-55-01']);
        Vehicle::factory()->create(['license_plate' => 'XYZ-55-02']);
        Vehicle::factory()->create(['license_plate' => 'AAA-00-00']);

        $this->actingAs($admin)
            ->getJson(route('vehicles.search', ['plate' => 'xyz55', 'json' => 1]))
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
