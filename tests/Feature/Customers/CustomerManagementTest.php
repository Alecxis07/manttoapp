<?php

namespace Tests\Feature\Customers;

use App\Enums\CustomerStatus;
use App\Enums\CustomerType;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\CustomerFiscalProfile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_individual_customer(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post(route('customers.store'), [
            'type' => CustomerType::Individual->value,
            'name' => 'Juan Pérez',
            'phone' => '5512345678',
            'email' => 'juan@example.com',
            'status' => CustomerStatus::Active->value,
        ]);

        $customer = Customer::query()->where('email', 'juan@example.com')->first();

        $this->assertNotNull($customer);
        $response->assertRedirect(route('customers.show', $customer));
        $this->assertSame(CustomerStatus::Active, $customer->status);
        $this->assertSame($admin->id, $customer->created_by);
        $this->assertDatabaseHas('activity_logs', [
            'subject_type' => Customer::class,
            'subject_id' => $customer->id,
            'action' => 'created',
        ]);
    }

    public function test_admin_can_create_company_with_fiscal_profile(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $response = $this->actingAs($admin)->post(route('customers.store'), [
            'type' => CustomerType::Company->value,
            'name' => 'Transportes del Norte SA de CV',
            'trade_name' => 'TDN',
            'phone' => '5587654321',
            'email' => 'contacto@tdn.mx',
            'status' => CustomerStatus::Active->value,
            'fiscal_profiles' => [
                [
                    'legal_name' => 'Transportes del Norte SA de CV',
                    'rfc' => 'TDN850101ABC',
                    'tax_regime_code' => '601',
                    'cfdi_use_code' => 'G03',
                    'postal_code' => '64000',
                    'email' => 'facturacion@tdn.mx',
                    'is_default' => true,
                ],
            ],
        ]);

        $customer = Customer::query()->where('email', 'contacto@tdn.mx')->first();

        $this->assertNotNull($customer);
        $response->assertRedirect(route('customers.show', $customer));
        $this->assertSame(1, $customer->fiscalProfiles()->count());
        $this->assertTrue($customer->defaultFiscalProfile->is_default);
        $this->assertSame('TDN850101ABC', $customer->defaultFiscalProfile->rfc);
    }

    public function test_email_must_be_unique_case_insensitive(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        Customer::factory()->create(['email' => 'cliente@example.com']);

        $this->actingAs($admin)
            ->post(route('customers.store'), [
                'type' => CustomerType::Individual->value,
                'name' => 'Otro',
                'email' => 'CLIENTE@example.com',
                'status' => CustomerStatus::Active->value,
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_null_emails_do_not_collide_for_uniqueness(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        Customer::factory()->withoutEmail()->create();

        $this->actingAs($admin)
            ->post(route('customers.store'), [
                'type' => CustomerType::Individual->value,
                'name' => 'Sin email',
                'email' => null,
                'status' => CustomerStatus::Active->value,
            ])
            ->assertRedirect();

        $this->assertSame(2, Customer::query()->whereNull('email')->count());
    }

    public function test_update_ignores_own_email_for_uniqueness(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create(['email' => 'mismo@example.com']);

        $this->actingAs($admin)
            ->put(route('customers.update', $customer), [
                'type' => $customer->type->value,
                'name' => 'Nombre actualizado',
                'email' => 'MISMO@example.com',
                'status' => CustomerStatus::Active->value,
                'fiscal_profiles' => [],
            ])
            ->assertRedirect(route('customers.show', $customer));

        $this->assertSame('mismo@example.com', $customer->fresh()->email);
        $this->assertSame('Nombre actualizado', $customer->fresh()->name);
    }

    public function test_only_one_default_fiscal_profile_per_customer(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();

        $this->actingAs($admin)
            ->put(route('customers.update', $customer), [
                'type' => $customer->type->value,
                'name' => $customer->name,
                'email' => $customer->email,
                'status' => CustomerStatus::Active->value,
                'fiscal_profiles' => [
                    [
                        'legal_name' => 'Perfil A',
                        'rfc' => 'AAA010101AAA',
                        'tax_regime_code' => '601',
                        'cfdi_use_code' => 'G03',
                        'postal_code' => '01000',
                        'is_default' => true,
                    ],
                    [
                        'legal_name' => 'Perfil B',
                        'rfc' => 'BBB010101BBB',
                        'tax_regime_code' => '601',
                        'cfdi_use_code' => 'G03',
                        'postal_code' => '02000',
                        'is_default' => false,
                    ],
                ],
            ])
            ->assertRedirect(route('customers.show', $customer));

        $this->assertSame(1, $customer->fiscalProfiles()->where('is_default', true)->count());
        $this->assertSame(2, $customer->fiscalProfiles()->count());
    }

    public function test_reject_multiple_default_fiscal_profiles_in_request(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)
            ->post(route('customers.store'), [
                'type' => CustomerType::Company->value,
                'name' => 'Empresa',
                'trade_name' => 'Emp',
                'status' => CustomerStatus::Active->value,
                'fiscal_profiles' => [
                    [
                        'legal_name' => 'A',
                        'rfc' => 'AAA010101AAA',
                        'tax_regime_code' => '601',
                        'cfdi_use_code' => 'G03',
                        'postal_code' => '01000',
                        'is_default' => true,
                    ],
                    [
                        'legal_name' => 'B',
                        'rfc' => 'BBB010101BBB',
                        'tax_regime_code' => '601',
                        'cfdi_use_code' => 'G03',
                        'postal_code' => '02000',
                        'is_default' => true,
                    ],
                ],
            ])
            ->assertSessionHasErrors('fiscal_profiles');
    }

    public function test_deactivate_keeps_history_and_record(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create(['status' => CustomerStatus::Active]);
        CustomerFiscalProfile::factory()->create([
            'customer_id' => $customer->id,
            'is_default' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $customer->refresh();

        $this->assertSame(CustomerStatus::Inactive, $customer->status);
        $this->assertNull($customer->deleted_at);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertTrue(
            ActivityLog::query()
                ->where('subject_type', Customer::class)
                ->where('subject_id', $customer->id)
                ->where('action', 'updated')
                ->exists()
        );

        $this->actingAs($admin)
            ->get(route('customers.show', $customer))
            ->assertOk();
    }

    public function test_index_supports_search_status_and_pagination(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        Customer::factory()->create(['name' => 'Alpha Transportes', 'status' => CustomerStatus::Active]);
        Customer::factory()->inactive()->create(['name' => 'Beta Logistics']);

        $this->actingAs($admin)
            ->get(route('customers.index', ['search' => 'Alpha', 'status' => 'active']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customers/Index')
                ->has('customers.data', 1)
                ->where('customers.data.0.name', 'Alpha Transportes'));
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
