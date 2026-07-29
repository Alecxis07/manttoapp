<?php

namespace Tests\Feature\Quotations;

use App\Models\Quotation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_pdf_download_contains_stored_amounts(): void
    {
        $admin = $this->makeUserWithRole(RolesAndPermissionsSeeder::ROLE_ADMIN);
        $quotation = Quotation::factory()->sent()->withServiceItem()->create([
            'subtotal' => '1000.00',
            'discount_total' => '100.00',
            'tax_total' => '144.00',
            'total' => '1044.00',
            'tax_rate' => '16.00',
        ]);

        // Force stored amounts after withServiceItem recalculation.
        $quotation->forceFill([
            'subtotal' => '1000.00',
            'discount_total' => '100.00',
            'tax_total' => '144.00',
            'total' => '1044.00',
            'tax_rate' => '16.00',
        ])->save();

        $response = $this->actingAs($admin)->get(route('quotations.pdf', $quotation));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));

        $filename = sprintf('%s-v%d.pdf', $quotation->folio, $quotation->version);
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString($filename, (string) $response->headers->get('content-disposition'));

        // Render HTML view used by PDF to assert exact stored amounts (RF-COT-008).
        $html = view('pdf.quotation', ['quotation' => $quotation->fresh(['items', 'customer', 'vehicle'])])->render();
        $this->assertStringContainsString('1,000.00', $html);
        $this->assertStringContainsString('100.00', $html);
        $this->assertStringContainsString('144.00', $html);
        $this->assertStringContainsString('1,044.00', $html);
        $this->assertStringContainsString($quotation->folio, $html);
    }

    protected function makeUserWithRole(string $role): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->assignRole($role);

        return $user;
    }
}
