<?php

namespace App\Http\Requests;

use App\DTOs\ServiceCategoryData;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ServiceCategory $category */
        $category = $this->route('service_category');

        return $this->user()?->can('update', $category) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var ServiceCategory $category */
        $category = $this->route('service_category');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('service_categories', 'code')->ignore($category->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function toDto(): ServiceCategoryData
    {
        /** @var array{code: string, name: string, description?: string|null, is_active: bool} $data */
        $data = $this->validated();

        return new ServiceCategoryData(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? null,
            isActive: (bool) $data['is_active'],
        );
    }
}
