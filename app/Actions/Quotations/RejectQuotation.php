<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectQuotation
{
    public function handle(Quotation $quotation, User $actor, ?string $reason = null): Quotation
    {
        if (! $quotation->status->canTransitionTo(QuotationStatus::Rejected)) {
            throw ValidationException::withMessages([
                'status' => __('La cotización no puede rechazarse desde el estado :status.', [
                    'status' => $quotation->status->label(),
                ]),
            ]);
        }

        return DB::transaction(function () use ($quotation, $actor, $reason): Quotation {
            $from = $quotation->status;

            $quotation->update([
                'status' => QuotationStatus::Rejected,
                'rejected_at' => now(),
                'rejected_by' => $actor->id,
                'rejection_reason' => $reason,
                'updated_by' => $actor->id,
            ]);

            QuotationStatusHistory::query()->create([
                'quotation_id' => $quotation->id,
                'from_status' => $from,
                'to_status' => QuotationStatus::Rejected,
                'user_id' => $actor->id,
                'notes' => $reason ?? 'Cotización rechazada',
                'created_at' => now(),
            ]);

            return $quotation->fresh(['items', 'customer', 'vehicle']);
        });
    }
}
