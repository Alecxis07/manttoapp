<?php

namespace App\Actions\Vehicles;

use App\Models\Vehicle;
use App\Services\VehicleExpedienteTimeline;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;

class ExportVehicleHistoryPdf
{
    public function __construct(
        private VehicleExpedienteTimeline $timeline,
    ) {}

    /**
     * Export vehicle expediente timeline as PDF (RF-HIS-007). Sync is acceptable for MVP (RNF-REN-005).
     *
     * @param  list<string>|null  $types
     */
    public function handle(
        Vehicle $vehicle,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
        ?array $types = null,
    ): Response {
        $vehicle->loadMissing(['customer', 'vehicleType']);

        $entries = $this->timeline->build($vehicle, $from, $to, $types);

        $pdf = Pdf::loadView('pdf.vehicle-history', [
            'vehicle' => $vehicle,
            'customer' => $vehicle->customer,
            'entries' => $entries,
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'types' => $types,
            ],
            'generatedAt' => now(),
        ]);

        $filename = sprintf(
            'expediente-%s-%s.pdf',
            $vehicle->license_plate_normalized,
            now()->format('Ymd-His')
        );

        return $pdf->download($filename);
    }
}
