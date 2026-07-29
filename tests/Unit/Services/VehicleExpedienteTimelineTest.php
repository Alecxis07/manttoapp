<?php

namespace Tests\Unit\Services;

use App\Enums\ExpedienteEventType;
use App\Enums\QuotationStatus;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleExpedienteTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VehicleExpedienteTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_consolidates_orders_quotations_and_evidences_descending(): void
    {
        $vehicle = Vehicle::factory()->create();

        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'folio' => 'ORD-2026-00001',
            'received_at' => Carbon::parse('2026-01-10 10:00:00'),
            'total' => '500.00',
        ]);

        Quotation::factory()->create([
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'folio' => 'COT-2026-00001',
            'status' => QuotationStatus::Sent,
            'issued_at' => Carbon::parse('2026-02-15 12:00:00'),
            'total' => '1160.00',
        ]);

        Attachment::query()->create([
            'attachable_type' => $vehicle->getMorphClass(),
            'attachable_id' => $vehicle->id,
            'disk' => 'local',
            'path' => 'vehicles/doc.pdf',
            'original_name' => 'factura-unidad.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'uploaded_by' => User::factory()->create()->id,
        ]);

        Attachment::query()
            ->where('original_name', 'factura-unidad.pdf')
            ->update([
                'created_at' => Carbon::parse('2026-03-01 09:00:00'),
                'updated_at' => Carbon::parse('2026-03-01 09:00:00'),
            ]);

        $timeline = app(VehicleExpedienteTimeline::class)->build($vehicle);

        $this->assertCount(3, $timeline);
        $this->assertSame(ExpedienteEventType::Evidence->value, $timeline[0]['type']);
        $this->assertSame(ExpedienteEventType::Quotation->value, $timeline[1]['type']);
        $this->assertSame(ExpedienteEventType::Order->value, $timeline[2]['type']);
        $this->assertSame('ORD-2026-00001', $timeline[2]['title']);
        $this->assertSame('500.00', $timeline[2]['amount']);
    }

    public function test_filters_by_date_range_and_type(): void
    {
        $customer = Customer::factory()->create();
        $vehicle = Vehicle::factory()->create(['customer_id' => $customer->id]);

        MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'received_at' => Carbon::parse('2026-01-05 08:00:00'),
        ]);

        Quotation::factory()->create([
            'customer_id' => $vehicle->customer_id,
            'vehicle_id' => $vehicle->id,
            'issued_at' => Carbon::parse('2026-02-20 08:00:00'),
        ]);

        $timeline = app(VehicleExpedienteTimeline::class)->build(
            $vehicle,
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-02-28'),
            [ExpedienteEventType::Quotation->value],
        );

        $this->assertCount(1, $timeline);
        $this->assertSame(ExpedienteEventType::Quotation->value, $timeline[0]['type']);
    }

    public function test_includes_order_attachments_as_evidence(): void
    {
        $vehicle = Vehicle::factory()->create();
        $order = MaintenanceOrder::factory()->forVehicle($vehicle)->create([
            'received_at' => Carbon::parse('2026-01-01 10:00:00'),
        ]);

        Attachment::query()->create([
            'attachable_type' => $order->getMorphClass(),
            'attachable_id' => $order->id,
            'disk' => 'local',
            'path' => 'orders/photo.jpg',
            'original_name' => 'evidencia-orden.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 2048,
            'uploaded_by' => null,
        ]);

        Attachment::query()
            ->where('original_name', 'evidencia-orden.jpg')
            ->update([
                'created_at' => Carbon::parse('2026-01-02 11:00:00'),
                'updated_at' => Carbon::parse('2026-01-02 11:00:00'),
            ]);

        $timeline = app(VehicleExpedienteTimeline::class)->build(
            $vehicle,
            types: [ExpedienteEventType::Evidence->value],
        );

        $this->assertCount(1, $timeline);
        $this->assertSame('evidencia-orden.jpg', $timeline[0]['title']);
    }
}
