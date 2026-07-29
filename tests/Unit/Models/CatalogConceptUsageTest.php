<?php

namespace Tests\Unit\Models;

use App\Contracts\CatalogConcept;
use App\Models\PartCatalog;
use App\Models\ServiceCatalog;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogConceptUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_implement_catalog_concept_contract(): void
    {
        $this->assertTrue(is_subclass_of(ServiceCategory::class, CatalogConcept::class));
        $this->assertTrue(is_subclass_of(ServiceCatalog::class, CatalogConcept::class));
        $this->assertTrue(is_subclass_of(PartCatalog::class, CatalogConcept::class));
    }

    public function test_service_and_part_is_in_use_stub_returns_false_before_order_items_exist(): void
    {
        $service = ServiceCatalog::factory()->create();
        $part = PartCatalog::factory()->create();

        // RN-CAT-001: usage against maintenance_order_items / quotation_items
        // is wired when those tables exist (Fases 5/7). Stub must stay safe.
        $this->assertFalse($service->isInUse());
        $this->assertFalse($part->isInUse());
    }

    public function test_category_is_in_use_when_it_has_services(): void
    {
        $category = ServiceCategory::factory()->create();
        ServiceCatalog::factory()->create(['service_category_id' => $category->id]);

        $this->assertTrue($category->isInUse());
    }
}
