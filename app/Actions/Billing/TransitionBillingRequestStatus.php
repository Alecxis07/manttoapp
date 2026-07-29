<?php

namespace App\Actions\Billing;

use App\Enums\BillingRequestStatus;
use App\Models\BillingRequest;
use App\Models\BillingRequestStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionBillingRequestStatus
{
    public function handle(
        BillingRequest $billingRequest,
        BillingRequestStatus $to,
        User $actor,
        ?string $notes = null,
    ): BillingRequest {
        if ($billingRequest->isImmutable()) {
            throw ValidationException::withMessages([
                'status' => __('La solicitud es inmutable en su estado actual (RN-FAC-003).'),
            ]);
        }

        if ($to === BillingRequestStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => __('Use la acción de envío a revisión.'),
            ]);
        }

        if ($to === BillingRequestStatus::Processed) {
            throw ValidationException::withMessages([
                'status' => __('Use la acción de marcar como procesada con referencia de factura.'),
            ]);
        }

        if (! $billingRequest->status->canTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => __('Transición de estado no permitida.'),
            ]);
        }

        return DB::transaction(function () use ($billingRequest, $to, $actor, $notes): BillingRequest {
            $from = $billingRequest->status;

            $billingRequest->update([
                'status' => $to,
            ]);

            BillingRequestStatusHistory::query()->create([
                'billing_request_id' => $billingRequest->id,
                'from_status' => $from,
                'to_status' => $to,
                'user_id' => $actor->id,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            return $billingRequest->fresh(['items', 'customer', 'vehicle', 'statusHistory']);
        });
    }
}
