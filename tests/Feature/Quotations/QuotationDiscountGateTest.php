<?php

namespace Tests\Feature\Quotations;

use App\Enums\QuotationItemType;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use App\Support\DiscountLimiter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class QuotationDiscountGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_tecnico_cannot_apply_discount_above_zero(): void
    {
        $tecnico = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_TECNICO);
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $service = ServiceCatalog::factory()->create(['base_price' => '1000.00']);

        $this->assertFalse(Gate::forUser($tecnico)->allows('approve-discount', 1));

        $this->actingAs($tecnico)
            ->post(route('quotations.store'), [
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'items' => [
                    [
                        'item_type' => QuotationItemType::Service->value,
                        'service_catalog_id' => $service->id,
                        'description' => $service->description,
                        'quantity' => 1,
                        'unit_price' => 1000,
                        'discount' => 50,
                    ],
                ],
            ])
            ->assertForbidden();

        $this->assertSame(0, Quotation::query()->count());
    }

    public function test_administrativo_can_apply_up_to_15_percent(): void
    {
        $user = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $service = ServiceCatalog::factory()->create(['base_price' => '1000.00']);

        $this->assertTrue(Gate::forUser($user)->allows('approve-discount', 15));
        $this->assertFalse(Gate::forUser($user)->allows('approve-discount', 15.01));

        $this->actingAs($user)
            ->post(route('quotations.store'), [
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'items' => [
                    [
                        'item_type' => QuotationItemType::Service->value,
                        'service_catalog_id' => $service->id,
                        'description' => $service->description,
                        'quantity' => 1,
                        'unit_price' => 1000,
                        'discount' => 150,
                    ],
                ],
            ])
            ->assertRedirect();

        $quotation = Quotation::query()->first();
        $this->assertNotNull($quotation);
        $this->assertSame('150.00', (string) $quotation->discount_total);

        $percent = app(DiscountLimiter::class)->percentOf(
            (string) $quotation->subtotal,
            (string) $quotation->discount_total
        );
        $this->assertEqualsWithDelta(15.0, $percent, 0.01);
    }

    public function test_admin_has_unlimited_discount(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->assertTrue(Gate::forUser($admin)->allows('approve-discount', 50));
        $this->assertTrue(Gate::forUser($admin)->allows('approve-discount', 100));
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
