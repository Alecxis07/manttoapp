<?php

namespace App\Http\Requests;

use App\Enums\BillingRequestStatus;
use App\Models\BillingRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionBillingRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var BillingRequest $billingRequest */
        $billingRequest = $this->route('billing_request');

        return $this->user()?->can('transition', $billingRequest) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    BillingRequestStatus::Incomplete->value,
                    BillingRequestStatus::Approved->value,
                    BillingRequestStatus::Rejected->value,
                    BillingRequestStatus::Cancelled->value,
                ]),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function targetStatus(): BillingRequestStatus
    {
        return BillingRequestStatus::from($this->validated('status'));
    }
}
