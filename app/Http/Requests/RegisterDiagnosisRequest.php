<?php

namespace App\Http\Requests;

use App\Models\MaintenanceOrder;
use Illuminate\Foundation\Http\FormRequest;

class RegisterDiagnosisRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MaintenanceOrder $order */
        $order = $this->route('maintenance_order');

        return $this->user()?->can('diagnose', $order) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'diagnosis' => ['required', 'string', 'max:10000'],
            'technical_notes' => ['nullable', 'string', 'max:10000'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
