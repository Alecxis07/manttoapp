<?php

namespace App\Http\Requests;

use App\DTOs\BillingRequestData;
use App\Models\BillingRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBillingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BillingRequest::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'maintenance_order_id' => ['nullable', 'integer', 'exists:maintenance_orders,id'],
            'quotation_id' => ['nullable', 'integer', 'exists:quotations,id'],
            'customer_fiscal_profile_id' => ['nullable', 'integer', 'exists:customer_fiscal_profiles,id'],
            'payment_method_code' => ['nullable', 'string', 'max:10'],
            'payment_form_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $orderId = $this->input('maintenance_order_id');
            $quotationId = $this->input('quotation_id');

            if (blank($orderId) && blank($quotationId)) {
                $validator->errors()->add(
                    'origin',
                    __('Debe indicar una orden o una cotización de origen (RN-FAC-001).')
                );
            }

            if (filled($orderId) && filled($quotationId)) {
                $validator->errors()->add(
                    'origin',
                    __('Indique solo un origen: orden o cotización.')
                );
            }
        });
    }

    public function toDto(): BillingRequestData
    {
        /** @var array{
         *     maintenance_order_id?: int|null,
         *     quotation_id?: int|null,
         *     customer_fiscal_profile_id?: int|null,
         *     payment_method_code?: string|null,
         *     payment_form_code?: string|null,
         *     notes?: string|null
         * } $data
         */
        $data = $this->validated();

        return new BillingRequestData(
            maintenanceOrderId: isset($data['maintenance_order_id']) ? (int) $data['maintenance_order_id'] : null,
            quotationId: isset($data['quotation_id']) ? (int) $data['quotation_id'] : null,
            customerFiscalProfileId: isset($data['customer_fiscal_profile_id']) ? (int) $data['customer_fiscal_profile_id'] : null,
            paymentMethodCode: $data['payment_method_code'] ?? null,
            paymentFormCode: $data['payment_form_code'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
