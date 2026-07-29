<?php

namespace App\Actions\Billing;

use App\Enums\BillingRequestStatus;
use App\Models\BillingRequest;
use App\Models\BillingRequestStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitForReview
{
    public function handle(BillingRequest $billingRequest, User $actor, ?string $notes = null): BillingRequest
    {
        if (! $billingRequest->status->canTransitionTo(BillingRequestStatus::PendingReview)) {
            throw ValidationException::withMessages([
                'status' => __('La solicitud no puede enviarse a revisión desde su estado actual.'),
            ]);
        }

        if (! $billingRequest->hasCompleteFiscalData()) {
            throw ValidationException::withMessages([
                'fiscal_profile_snapshot' => __('Datos fiscales incompletos: se requieren razón social, RFC, régimen fiscal, uso de CFDI y código postal (RN-FAC-002).'),
            ]);
        }

        if ($billingRequest->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => __('La solicitud debe incluir al menos un concepto.'),
            ]);
        }

        if (blank($billingRequest->payment_method_code) || blank($billingRequest->payment_form_code)) {
            throw ValidationException::withMessages([
                'payment_method_code' => __('Indique método y forma de pago antes de enviar a revisión.'),
            ]);
        }

        return DB::transaction(function () use ($billingRequest, $actor, $notes): BillingRequest {
            $from = $billingRequest->status;

            $billingRequest->update([
                'status' => BillingRequestStatus::PendingReview,
            ]);

            BillingRequestStatusHistory::query()->create([
                'billing_request_id' => $billingRequest->id,
                'from_status' => $from,
                'to_status' => BillingRequestStatus::PendingReview,
                'user_id' => $actor->id,
                'notes' => $notes ?: 'Enviada a revisión',
                'created_at' => now(),
            ]);

            return $billingRequest->fresh(['items', 'customer', 'vehicle', 'statusHistory']);
        });
    }
}
