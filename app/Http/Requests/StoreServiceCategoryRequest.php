<?php

namespace App\Http\Requests;

use App\DTOs\ServiceCategoryData;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ServiceCategory::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:service_categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): ServiceCategoryData
    {
        /** @var array{code: string, name: string, description?: string|null, is_active?: bool} $data */
        $data = $this->validated();

        return new ServiceCategoryData(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }
}
