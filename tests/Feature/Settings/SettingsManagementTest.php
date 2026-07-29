<?php

namespace Tests\Feature\Settings;

use App\Actions\Maintenance\UploadOrderAttachment;
use App\Models\ActivityLog;
use App\Models\DocumentSequence;
use App\Models\Setting;
use App\Models\User;
use App\Services\TotalsCalculator;
use App\Support\FolioGenerator;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('settings.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings/Edit')
                ->has('settings')
                ->has('sequences', 3)
            );
    }

    public function test_non_admin_cannot_view_or_update_settings(): void
    {
        $user = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO);

        $this->actingAs($user)
            ->get(route('settings.edit'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('settings.update'), $this->validPayload())
            ->assertForbidden();
    }

    public function test_admin_can_update_iva_attachments_and_folio_prefixes(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)
            ->put(route('settings.update'), $this->validPayload([
                'tax_iva_rate' => '8.00',
                'attachments_max_size_kb' => 2048,
                'attachments_allowed_mimes' => "image/png\napplication/pdf",
                'sequences' => [
                    [
                        'document_type' => 'maintenance_order',
                        'prefix' => 'MTO',
                        'padding' => 6,
                        'reset_counter' => false,
                    ],
                    [
                        'document_type' => 'quotation',
                        'prefix' => 'COT',
                        'padding' => 5,
                        'reset_counter' => false,
                    ],
                    [
                        'document_type' => 'billing_request',
                        'prefix' => 'FAC',
                        'padding' => 5,
                        'reset_counter' => false,
                    ],
                ],
            ]))
            ->assertRedirect(route('settings.edit'));

        $this->assertSame('8.00', Setting::query()->where('key', TotalsCalculator::IVA_SETTING_KEY)->value('value'));
        $this->assertSame('2048', Setting::query()->where('key', UploadOrderAttachment::MAX_SIZE_SETTING_KEY)->value('value'));
        $this->assertSame(
            ['image/png', 'application/pdf'],
            json_decode((string) Setting::query()->where('key', UploadOrderAttachment::ALLOWED_MIMES_SETTING_KEY)->value('value'), true)
        );

        $orderSequence = DocumentSequence::query()->where('document_type', 'maintenance_order')->first();
        $this->assertNotNull($orderSequence);
        $this->assertSame('MTO', $orderSequence->prefix);
        $this->assertSame(6, $orderSequence->padding);

        $folio = app(FolioGenerator::class)->generate('maintenance_order');
        $this->assertStringStartsWith('MTO-', $folio);

        $this->assertTrue(
            ActivityLog::query()
                ->whereIn('action', ['created', 'updated'])
                ->where(function ($query): void {
                    $query->where('subject_type', (new Setting)->getMorphClass())
                        ->orWhere('subject_type', (new DocumentSequence)->getMorphClass());
                })
                ->exists()
        );
    }

    public function test_new_documents_use_updated_iva_rate(): void
    {
        Setting::query()->create([
            'key' => TotalsCalculator::IVA_SETTING_KEY,
            'value' => '16.00',
            'type' => 'decimal',
            'group' => 'tax',
        ]);

        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);

        $this->actingAs($admin)->put(route('settings.update'), $this->validPayload([
            'tax_iva_rate' => '8',
        ]))->assertRedirect(route('settings.edit'));

        $totals = app(TotalsCalculator::class)->calculate([
            ['quantity' => 1, 'unit_price' => 100, 'discount' => 0],
        ]);

        $this->assertSame('8.00', $totals['tax_rate']);
        $this->assertSame('8.00', $totals['tax_total']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'tax_iva_rate' => '16.00',
            'attachments_max_size_kb' => UploadOrderAttachment::DEFAULT_MAX_SIZE_KB,
            'attachments_allowed_mimes' => implode("\n", UploadOrderAttachment::DEFAULT_ALLOWED_MIMES),
            'sequences' => [
                [
                    'document_type' => 'maintenance_order',
                    'prefix' => 'ORD',
                    'padding' => 5,
                    'reset_counter' => false,
                ],
                [
                    'document_type' => 'quotation',
                    'prefix' => 'COT',
                    'padding' => 5,
                    'reset_counter' => false,
                ],
                [
                    'document_type' => 'billing_request',
                    'prefix' => 'FAC',
                    'padding' => 5,
                    'reset_counter' => false,
                ],
            ],
        ], $overrides);
    }

    private function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
