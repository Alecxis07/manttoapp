<?php

namespace App\Http\Requests;

use App\Enums\MaintenanceOrderStatus;
use App\Models\MaintenanceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionMaintenanceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MaintenanceOrder $order */
        $order = $this->route('maintenance_order');

        return $this->user()?->can('changeStatus', $order) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(MaintenanceOrderStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'cancellation_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
