<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('audit.view') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'entity' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array{entity: string, user_id: int|null, action: string, from: string|null, to: string|null}
     */
    public function filters(): array
    {
        return [
            'entity' => $this->string('entity')->toString(),
            'user_id' => $this->filled('user_id') ? $this->integer('user_id') : null,
            'action' => $this->string('action')->toString(),
            'from' => $this->date('from')?->toDateString(),
            'to' => $this->date('to')?->toDateString(),
        ];
    }
}
