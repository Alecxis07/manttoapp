<?php

namespace App\Http\Requests;

use App\Models\BillingRequest;
use Illuminate\Foundation\Http\FormRequest;

class MarkBillingRequestProcessedRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var BillingRequest $billingRequest */
        $billingRequest = $this->route('billing_request');

        return $this->user()?->can('process', $billingRequest) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'invoice_reference' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
