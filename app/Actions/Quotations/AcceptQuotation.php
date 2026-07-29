<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptQuotation
{
    public function handle(Quotation $quotation, User $actor): Quotation
    {
        if ($quotation->isExpiredByDate() || $quotation->status === QuotationStatus::Expired) {
            if ($quotation->status === QuotationStatus::Sent) {
                $quotation->update([
                    'status' => QuotationStatus::Expired,
                    'updated_by' => $actor->id,
                ]);
            }

            throw ValidationException::withMessages([
                'status' => __('Una cotización vencida no puede aceptarse (RN-COT-003).'),
            ]);
        }

        if (! $quotation->isLatestVersion()) {
            throw ValidationException::withMessages([
                'version' => __('Solo la versión más reciente de la cotización puede aceptarse (RN-COT-002).'),
            ]);
        }

        if (! $quotation->status->canTransitionTo(QuotationStatus::Accepted)) {
            throw ValidationException::withMessages([
                'status' => __('La cotización no puede aceptarse desde el estado :status.', [
                    'status' => $quotation->status->label(),
                ]),
            ]);
        }

        return DB::transaction(function () use ($quotation, $actor): Quotation {
            $from = $quotation->status;

            $quotation->update([
                'status' => QuotationStatus::Accepted,
                'accepted_at' => now(),
                'accepted_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            QuotationStatusHistory::query()->create([
                'quotation_id' => $quotation->id,
                'from_status' => $from,
                'to_status' => QuotationStatus::Accepted,
                'user_id' => $actor->id,
                'notes' => 'Cotización aceptada',
                'created_at' => now(),
            ]);

            return $quotation->fresh(['items', 'customer', 'vehicle']);
        });
    }
}
