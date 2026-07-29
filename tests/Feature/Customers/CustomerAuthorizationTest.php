<?php

namespace Tests\Feature\Customers;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_administrativo_can_create_and_update_but_not_hard_delete_permission(): void
    {
        $user = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);

        $this->assertTrue($user->can('customers.create'));
        $this->assertTrue($user->can('customers.update'));
        $this->assertTrue($user->can('customers.read'));
        $this->assertFalse($user->can('customers.delete'));

        $this->actingAs($user)
            ->get(route('customers.index'))
            ->assertOk();

        $response = $this->actingAs($user)->post(route('customers.store'), [
            'type' => CustomerType::Individual->value,
            'name' => 'Cliente Adminvo',
            'email' => 'adminvo@example.com',
            'status' => CustomerStatus::Active->value,
        ]);

        $customer = Customer::query()->where('email', 'adminvo@example.com')->first();
        $this->assertNotNull($customer);
        $response->assertRedirect(route('customers.show', $customer));

        $this->actingAs($user)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertSame(CustomerStatus::Inactive, $customer->fresh()->status);
    }

    public function test_tecnico_can_read_but_cannot_create_or_update(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $customer = Customer::factory()->create();

        $this->actingAs($tecnico)
            ->get(route('customers.index'))
            ->assertOk();

        $this->actingAs($tecnico)
            ->get(route('customers.show', $customer))
            ->assertOk();

        $this->actingAs($tecnico)
            ->get(route('customers.create'))
            ->assertForbidden();

        $this->actingAs($tecnico)
            ->post(route('customers.store'), [
                'type' => CustomerType::Individual->value,
                'name' => 'Hack',
                'status' => CustomerStatus::Active->value,
            ])
            ->assertForbidden();

        $this->actingAs($tecnico)
            ->put(route('customers.update', $customer), [
                'type' => $customer->type->value,
                'name' => 'Hack',
                'status' => CustomerStatus::Active->value,
            ])
            ->assertForbidden();

        $this->actingAs($tecnico)
            ->delete(route('customers.destroy', $customer))
            ->assertForbidden();
    }

    public function test_consulta_is_read_only_for_customers(): void
    {
        $consulta = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);
        $customer = Customer::factory()->create();

        $this->actingAs($consulta)
            ->get(route('customers.index'))
            ->assertOk();

        $this->actingAs($consulta)
            ->post(route('customers.store'), [
                'type' => CustomerType::Individual->value,
                'name' => 'Hack',
                'status' => CustomerStatus::Active->value,
            ])
            ->assertForbidden();

        $this->actingAs($consulta)
            ->delete(route('customers.destroy', $customer))
            ->assertForbidden();
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
