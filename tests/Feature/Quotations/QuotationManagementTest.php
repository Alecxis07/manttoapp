<?php

namespace Tests\Feature\Quotations;

use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class QuotationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_create_quotation_with_folio_and_server_totals(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $service = ServiceCatalog::factory()->create(['base_price' => '1000.00']);

        $response = $this->actingAs($admin)->post(route('quotations.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'valid_until' => now()->addDays(10)->toDateString(),
            'commercial_terms' => 'Pago 50% anticipo',
            'items' => [
                [
                    'item_type' => QuotationItemType::Service->value,
                    'service_catalog_id' => $service->id,
                    'description' => $service->description,
                    'quantity' => 2,
                    'unit_price' => 1000,
                    'discount' => 0,
                ],
            ],
        ]);

        $quotation = Quotation::query()->first();

        $this->assertNotNull($quotation);
        $response->assertRedirect(route('quotations.show', $quotation));
        $this->assertMatchesRegularExpression('/^COT-\d{4}-\d{5}$/', $quotation->folio);
        $this->assertSame(QuotationStatus::Draft, $quotation->status);
        $this->assertSame('2000.00', (string) $quotation->subtotal);
        $this->assertSame('0.00', (string) $quotation->discount_total);
        $this->assertSame('320.00', (string) $quotation->tax_total);
        $this->assertSame('2320.00', (string) $quotation->total);
        $this->assertSame(1, $quotation->items()->count());
    }

    public function test_index_requires_read_permission(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('quotations.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Quotations/Index'));
    }

    public function test_consulta_cannot_create_quotation(): void
    {
        $consulta = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_CONSULTA);
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $service = ServiceCatalog::factory()->create();

        $this->actingAs($consulta)
            ->post(route('quotations.store'), [
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'items' => [
                    [
                        'item_type' => QuotationItemType::Service->value,
                        'service_catalog_id' => $service->id,
                        'description' => 'X',
                        'quantity' => 1,
                        'unit_price' => 100,
                        'discount' => 0,
                    ],
                ],
            ])
            ->assertForbidden();
    }

    public function test_vehicle_must_belong_to_customer(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $customer = Customer::factory()->create();
        $otherVehicle = Vehicle::factory()->create();
        $service = ServiceCatalog::factory()->create();

        $this->actingAs($admin)
            ->post(route('quotations.store'), [
                'customer_id' => $customer->id,
                'vehicle_id' => $otherVehicle->id,
                'items' => [
                    [
                        'item_type' => QuotationItemType::Service->value,
                        'service_catalog_id' => $service->id,
                        'description' => 'X',
                        'quantity' => 1,
                        'unit_price' => 100,
                        'discount' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors('vehicle_id');
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
