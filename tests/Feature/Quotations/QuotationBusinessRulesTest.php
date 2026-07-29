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
use Tests\TestCase;

class QuotationBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_sent_quotation_is_immutable_and_must_be_versioned_rn_cot_001(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $quotation = $this->createSentQuotation($admin);

        $this->actingAs($admin)
            ->put(route('quotations.update', $quotation), [
                'customer_id' => $quotation->customer_id,
                'vehicle_id' => $quotation->vehicle_id,
                'items' => [
                    [
                        'item_type' => QuotationItemType::Service->value,
                        'service_catalog_id' => $quotation->items->first()->service_catalog_id,
                        'description' => 'Intento de edición',
                        'quantity' => 1,
                        'unit_price' => 500,
                        'discount' => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors('status');

        $this->actingAs($admin)
            ->post(route('quotations.version', $quotation))
            ->assertRedirect();

        $version = Quotation::query()
            ->where('folio', $quotation->folio)
            ->where('version', 2)
            ->first();

        $this->assertNotNull($version);
        $this->assertSame(QuotationStatus::Draft, $version->status);
        $this->assertSame($quotation->id, $version->parent_quotation_id);
    }

    public function test_only_latest_version_can_be_accepted_rn_cot_002(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $v1 = $this->createSentQuotation($admin);

        $this->actingAs($admin)->post(route('quotations.version', $v1));
        $v2 = Quotation::query()->where('folio', $v1->folio)->where('version', 2)->firstOrFail();

        $this->actingAs($admin)->post(route('quotations.send', $v2))->assertRedirect();

        $this->actingAs($admin)
            ->post(route('quotations.accept', $v1))
            ->assertSessionHasErrors('version');

        $this->actingAs($admin)
            ->post(route('quotations.accept', $v2->fresh()))
            ->assertRedirect(route('quotations.show', $v2));

        $this->assertSame(QuotationStatus::Accepted, $v2->fresh()->status);
    }

    public function test_expired_quotation_cannot_be_accepted_rn_cot_003(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $quotation = $this->createSentQuotation($admin, validUntil: now()->subDay()->toDateString());

        $this->actingAs($admin)
            ->post(route('quotations.accept', $quotation))
            ->assertSessionHasErrors('status');
    }

    private function createSentQuotation(User $actor, ?string $validUntil = null): Quotation
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);
        $service = ServiceCatalog::factory()->create(['base_price' => '100.00']);

        $this->actingAs($actor)->post(route('quotations.store'), [
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'valid_until' => now()->addDays(10)->toDateString(),
            'items' => [
                [
                    'item_type' => QuotationItemType::Service->value,
                    'service_catalog_id' => $service->id,
                    'description' => $service->description,
                    'quantity' => 1,
                    'unit_price' => 100,
                    'discount' => 0,
                ],
            ],
        ]);

        $quotation = Quotation::query()->latest('id')->firstOrFail();

        $this->actingAs($actor)->post(route('quotations.send', $quotation));

        if ($validUntil !== null) {
            $quotation->forceFill(['valid_until' => $validUntil])->save();
        }

        return $quotation->fresh(['items']);
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
