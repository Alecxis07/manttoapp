<?php

namespace App\Http\Requests;

use App\Enums\ExpedienteEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterVehicleHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'type' => ['nullable', 'string', Rule::enum(ExpedienteEventType::class)],
            'types' => ['nullable', 'array'],
            'types.*' => ['string', Rule::enum(ExpedienteEventType::class)],
        ];
    }

    /**
     * @return list<string>|null
     */
    public function typesFilter(): ?array
    {
        $types = $this->input('types');

        if (is_array($types) && $types !== []) {
            return array_values(array_unique(array_map('strval', $types)));
        }

        $single = $this->string('type')->toString();

        if ($single !== '') {
            return [$single];
        }

        return null;
    }
}
