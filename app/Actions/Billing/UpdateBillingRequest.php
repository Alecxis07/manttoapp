<?php

namespace App\Actions\Billing;

use App\DTOs\BillingRequestData;
use App\Models\BillingRequest;
use App\Models\CustomerFiscalProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBillingRequest
{
    public function handle(BillingRequest $billingRequest, BillingRequestData $data, User $actor): BillingRequest
    {
        if (! $billingRequest->isEditable()) {
            throw ValidationException::withMessages([
                'status' => __('Solo las solicitudes en borrador o incompletas pueden editarse.'),
            ]);
        }

        if ($billingRequest->isImmutable()) {
            throw ValidationException::withMessages([
                'status' => __('La solicitud procesada es inmutable (RN-FAC-003).'),
            ]);
        }

        return DB::transaction(function () use ($billingRequest, $data): BillingRequest {
            $updates = [
                'payment_method_code' => $data->paymentMethodCode,
                'payment_form_code' => $data->paymentFormCode,
                'notes' => $data->notes,
            ];

            if ($data->customerFiscalProfileId !== null) {
                $profile = CustomerFiscalProfile::query()
                    ->where('customer_id', $billingRequest->customer_id)
                    ->whereKey($data->customerFiscalProfileId)
                    ->first();

                if ($profile === null) {
                    throw ValidationException::withMessages([
                        'customer_fiscal_profile_id' => __('El perfil fiscal no pertenece al cliente.'),
                    ]);
                }

                $updates['customer_fiscal_profile_id'] = $profile->id;
                $updates['fiscal_profile_snapshot'] = [
                    'legal_name' => $profile->legal_name,
                    'rfc' => $profile->rfc,
                    'tax_regime_code' => $profile->tax_regime_code,
                    'cfdi_use_code' => $profile->cfdi_use_code,
                    'postal_code' => $profile->postal_code,
                    'email' => $profile->email,
                    'snapshotted_at' => now()->toIso8601String(),
                ];
            }

            $billingRequest->update($updates);

            return $billingRequest->fresh(['items', 'customer', 'vehicle']);
        });
    }
}
