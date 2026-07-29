<?php

namespace App\Http\Requests;

use App\DTOs\BillingRequestData;
use App\Models\BillingRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var BillingRequest $billingRequest */
        $billingRequest = $this->route('billing_request');

        return $this->user()?->can('update', $billingRequest) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_fiscal_profile_id' => ['nullable', 'integer', 'exists:customer_fiscal_profiles,id'],
            'payment_method_code' => ['nullable', 'string', 'max:10'],
            'payment_form_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function toDto(): BillingRequestData
    {
        /** @var array{
         *     customer_fiscal_profile_id?: int|null,
         *     payment_method_code?: string|null,
         *     payment_form_code?: string|null,
         *     notes?: string|null
         * } $data
         */
        $data = $this->validated();

        return new BillingRequestData(
            maintenanceOrderId: null,
            quotationId: null,
            customerFiscalProfileId: isset($data['customer_fiscal_profile_id']) ? (int) $data['customer_fiscal_profile_id'] : null,
            paymentMethodCode: $data['payment_method_code'] ?? null,
            paymentFormCode: $data['payment_form_code'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
