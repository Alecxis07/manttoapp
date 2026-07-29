<?php

namespace Tests\Feature\Customers;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSelectorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_options_endpoint_returns_only_active_customers(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $active = Customer::factory()->create([
            'name' => 'Cliente Activo',
            'status' => CustomerStatus::Active,
        ]);
        Customer::factory()->inactive()->create([
            'name' => 'Cliente Inactivo',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('customers.options'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.name', 'Cliente Activo');

        $names = collect($response->json('data'))->pluck('name');
        $this->assertFalse($names->contains('Cliente Inactivo'));
    }

    public function test_options_endpoint_supports_search_among_active_only(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        Customer::factory()->create(['name' => 'Alpha Activo', 'status' => CustomerStatus::Active]);
        Customer::factory()->inactive()->create(['name' => 'Alpha Inactivo']);
        Customer::factory()->create(['name' => 'Beta Activo', 'status' => CustomerStatus::Active]);

        $this->actingAs($admin)
            ->getJson(route('customers.options', ['search' => 'Alpha']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alpha Activo');
    }

    public function test_scope_active_excludes_inactive_customers(): void
    {
        Customer::factory()->count(2)->create(['status' => CustomerStatus::Active]);
        Customer::factory()->inactive()->create();

        $this->assertSame(2, Customer::query()->active()->count());
        $this->assertSame(3, Customer::query()->count());
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
