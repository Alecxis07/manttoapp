<?php

namespace App\Actions\Quotations;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportQuotationPdf
{
    public function handle(Quotation $quotation): Response
    {
        $quotation->loadMissing(['items', 'customer', 'vehicle.vehicleType']);

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
        ]);

        $filename = sprintf('%s-v%d.pdf', $quotation->folio, $quotation->version);

        return $pdf->download($filename);
    }
}
