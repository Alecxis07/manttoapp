<?php

namespace App\Http\Requests;

use App\DTOs\PartCatalogData;
use App\Enums\PartCatalogType;
use App\Models\PartCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var PartCatalog $part */
        $part = $this->route('part_catalog');

        return $this->user()?->can('update', $part) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var PartCatalog $part */
        $part = $this->route('part_catalog');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('part_catalog', 'code')->ignore($part->id),
            ],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(PartCatalogType::class)],
            'service_category_id' => [
                'nullable',
                'integer',
                Rule::exists('service_categories', 'id')->where(function ($query) use ($part): void {
                    $query->where('is_active', true)
                        ->orWhere('id', $part->service_category_id);
                }),
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function toDto(): PartCatalogData
    {
        /** @var array{code: string, description: string, type: string, service_category_id?: int|null, base_price: numeric-string|float|int, unit_of_measure: string, is_active: bool} $data */
        $data = $this->validated();

        return new PartCatalogData(
            code: $data['code'],
            description: $data['description'],
            type: PartCatalogType::from($data['type']),
            serviceCategoryId: isset($data['service_category_id']) ? (int) $data['service_category_id'] : null,
            basePrice: number_format((float) $data['base_price'], 2, '.', ''),
            unitOfMeasure: $data['unit_of_measure'],
            isActive: (bool) $data['is_active'],
        );
    }
}
