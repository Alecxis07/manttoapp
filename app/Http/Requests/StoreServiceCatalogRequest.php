<?php

namespace App\Http\Requests;

use App\DTOs\ServiceCatalogData;
use App\Enums\ServiceCatalogType;
use App\Models\ServiceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ServiceCatalog::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('estimated_minutes') === '' || $this->input('estimated_minutes') === null) {
            $this->merge(['estimated_minutes' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:service_catalog,code'],
            'description' => ['required', 'string', 'max:255'],
            'service_category_id' => [
                'required',
                'integer',
                Rule::exists('service_categories', 'id')->where('is_active', true),
            ],
            'type' => ['required', Rule::enum(ServiceCatalogType::class)],
            'base_price' => ['required', 'numeric', 'min:0'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): ServiceCatalogData
    {
        /** @var array{code: string, description: string, service_category_id: int, type: string, base_price: numeric-string|float|int, unit_of_measure: string, estimated_minutes?: int|null, is_active?: bool} $data */
        $data = $this->validated();

        return new ServiceCatalogData(
            code: $data['code'],
            description: $data['description'],
            serviceCategoryId: (int) $data['service_category_id'],
            type: ServiceCatalogType::from($data['type']),
            basePrice: number_format((float) $data['base_price'], 2, '.', ''),
            unitOfMeasure: $data['unit_of_measure'],
            estimatedMinutes: isset($data['estimated_minutes']) ? (int) $data['estimated_minutes'] : null,
            isActive: $data['is_active'] ?? true,
        );
    }
}
