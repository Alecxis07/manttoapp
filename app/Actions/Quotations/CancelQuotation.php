<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelQuotation
{
    public function handle(Quotation $quotation, User $actor, ?string $reason = null): Quotation
    {
        if (! $quotation->status->canTransitionTo(QuotationStatus::Cancelled)) {
            throw ValidationException::withMessages([
                'status' => __('La cotización no puede cancelarse desde el estado :status.', [
                    'status' => $quotation->status->label(),
                ]),
            ]);
        }

        return DB::transaction(function () use ($quotation, $actor, $reason): Quotation {
            $from = $quotation->status;

            $quotation->update([
                'status' => QuotationStatus::Cancelled,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'updated_by' => $actor->id,
            ]);

            QuotationStatusHistory::query()->create([
                'quotation_id' => $quotation->id,
                'from_status' => $from,
                'to_status' => QuotationStatus::Cancelled,
                'user_id' => $actor->id,
                'notes' => $reason ?? 'Cotización cancelada',
                'created_at' => now(),
            ]);

            return $quotation->fresh(['items']);
        });
    }
}
