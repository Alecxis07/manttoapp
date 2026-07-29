<?php

namespace App\Actions\Quotations;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SendQuotation
{
    public function handle(Quotation $quotation, User $actor): Quotation
    {
        if (! $quotation->status->canTransitionTo(QuotationStatus::Sent)) {
            throw ValidationException::withMessages([
                'status' => __('Solo una cotización en borrador puede enviarse.'),
            ]);
        }

        if ($quotation->items()->count() === 0) {
            throw ValidationException::withMessages([
                'items' => __('La cotización debe tener al menos una partida.'),
            ]);
        }

        return DB::transaction(function () use ($quotation, $actor): Quotation {
            $from = $quotation->status;

            $quotation->update([
                'status' => QuotationStatus::Sent,
                'issued_at' => $quotation->issued_at ?? now(),
                'updated_by' => $actor->id,
            ]);

            QuotationStatusHistory::query()->create([
                'quotation_id' => $quotation->id,
                'from_status' => $from,
                'to_status' => QuotationStatus::Sent,
                'user_id' => $actor->id,
                'notes' => 'Cotización enviada',
                'created_at' => now(),
            ]);

            return $quotation->fresh(['items', 'customer', 'vehicle']);
        });
    }
}
