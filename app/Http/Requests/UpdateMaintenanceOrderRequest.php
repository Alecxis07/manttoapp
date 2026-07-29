<?php

namespace App\Http\Requests;

use App\Models\MaintenanceOrder;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MaintenanceOrder $order */
        $order = $this->route('maintenance_order');

        return $this->user()?->can('update', $order) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'required', 'string', 'max:5000'],
            'mileage' => ['sometimes', 'required', 'integer', 'min:0'],
            'technical_notes' => ['nullable', 'string', 'max:5000'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
