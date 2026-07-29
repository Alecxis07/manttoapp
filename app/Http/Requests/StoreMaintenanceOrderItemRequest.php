<?php

namespace App\Http\Requests;

use App\Models\MaintenanceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MaintenanceOrder $order */
        $order = $this->route('maintenance_order');

        return $this->user()?->can('addItems', $order) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_catalog_id' => [
                'nullable',
                'integer',
                Rule::exists('service_catalog', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['required_without:service_catalog_id', 'nullable', 'string', 'max:500'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
