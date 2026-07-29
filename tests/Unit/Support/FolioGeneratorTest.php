<?php

namespace Tests\Unit\Support;

use App\Support\FolioGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolioGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_sequential_folios_with_expected_format(): void
    {
        $generator = new FolioGenerator;
        $year = (int) now(config('app.timezone'))->format('Y');

        $first = $generator->generate('maintenance_order', $year);
        $second = $generator->generate('maintenance_order', $year);

        $this->assertSame(sprintf('ORD-%d-00001', $year), $first);
        $this->assertSame(sprintf('ORD-%d-00002', $year), $second);
    }

    public function test_uses_distinct_sequences_per_document_type(): void
    {
        $generator = new FolioGenerator;
        $year = 2026;

        $order = $generator->generate('maintenance_order', $year);
        $quotation = $generator->generate('quotation', $year);
        $billing = $generator->generate('billing_request', $year);

        $this->assertSame('ORD-2026-00001', $order);
        $this->assertSame('COT-2026-00001', $quotation);
        $this->assertSame('FAC-2026-00001', $billing);
    }

    public function test_resets_number_when_year_changes(): void
    {
        $generator = new FolioGenerator;

        $generator->generate('quotation', 2025);
        $generator->generate('quotation', 2025);

        $nextYear = $generator->generate('quotation', 2026);

        $this->assertSame('COT-2026-00001', $nextYear);
    }
}
