<?php

namespace App\Http\Requests;

use App\DTOs\PartCatalogData;
use App\Enums\PartCatalogType;
use App\Models\PartCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PartCatalog::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:part_catalog,code'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PartCatalogType::class)],
            'service_category_id' => [
                'nullable',
                'integer',
                Rule::exists('service_categories', 'id')->where('is_active', true),
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): PartCatalogData
    {
        /** @var array{code: string, description: string, type: string, service_category_id?: int|null, base_price: numeric-string|float|int, unit_of_measure: string, is_active?: bool} $data */
        $data = $this->validated();

        return new PartCatalogData(
            code: $data['code'],
            description: $data['description'],
            type: PartCatalogType::from($data['type']),
            serviceCategoryId: isset($data['service_category_id']) ? (int) $data['service_category_id'] : null,
            basePrice: number_format((float) $data['base_price'], 2, '.', ''),
            unitOfMeasure: $data['unit_of_measure'],
            isActive: $data['is_active'] ?? true,
        );
    }
}
