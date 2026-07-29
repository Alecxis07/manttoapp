<?php

namespace App\Actions\Billing;

use App\Enums\BillingRequestStatus;
use App\Models\BillingRequest;
use App\Models\BillingRequestStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkAsProcessed
{
    public function handle(
        BillingRequest $billingRequest,
        string $invoiceReference,
        User $actor,
        ?string $notes = null,
    ): BillingRequest {
        if (! $billingRequest->status->canTransitionTo(BillingRequestStatus::Processed)) {
            throw ValidationException::withMessages([
                'status' => __('Solo una solicitud aprobada puede marcarse como procesada.'),
            ]);
        }

        $invoiceReference = trim($invoiceReference);

        if ($invoiceReference === '') {
            throw ValidationException::withMessages([
                'invoice_reference' => __('La referencia de factura es obligatoria al procesar.'),
            ]);
        }

        return DB::transaction(function () use ($billingRequest, $invoiceReference, $actor, $notes): BillingRequest {
            $from = $billingRequest->status;

            $billingRequest->update([
                'status' => BillingRequestStatus::Processed,
                'invoice_reference' => $invoiceReference,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ]);

            BillingRequestStatusHistory::query()->create([
                'billing_request_id' => $billingRequest->id,
                'from_status' => $from,
                'to_status' => BillingRequestStatus::Processed,
                'user_id' => $actor->id,
                'notes' => $notes ?: 'Procesada con referencia '.$invoiceReference,
                'created_at' => now(),
            ]);

            return $billingRequest->fresh(['items', 'customer', 'vehicle', 'statusHistory']);
        });
    }
}
