<?php

namespace App\Http\Requests;

use App\DTOs\ServiceCatalogData;
use App\Enums\ServiceCatalogType;
use App\Models\ServiceCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ServiceCatalog $service */
        $service = $this->route('service_catalog');

        return $this->user()?->can('update', $service) ?? false;
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
        /** @var ServiceCatalog $service */
        $service = $this->route('service_catalog');

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('service_catalog', 'code')->ignore($service->id),
            ],
            'description' => ['required', 'string', 'max:255'],
            'service_category_id' => [
                'required',
                'integer',
                Rule::exists('service_categories', 'id')->where(function ($query) use ($service): void {
                    $query->where('is_active', true)
                        ->orWhere('id', $service->service_category_id);
                }),
            ],
            'type' => ['required', Rule::enum(ServiceCatalogType::class)],
            'base_price' => ['required', 'numeric', 'min:0'],
            'unit_of_measure' => ['required', 'string', 'max:50'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function toDto(): ServiceCatalogData
    {
        /** @var array{code: string, description: string, service_category_id: int, type: string, base_price: numeric-string|float|int, unit_of_measure: string, estimated_minutes?: int|null, is_active: bool} $data */
        $data = $this->validated();

        return new ServiceCatalogData(
            code: $data['code'],
            description: $data['description'],
            serviceCategoryId: (int) $data['service_category_id'],
            type: ServiceCatalogType::from($data['type']),
            basePrice: number_format((float) $data['base_price'], 2, '.', ''),
            unitOfMeasure: $data['unit_of_measure'],
            estimatedMinutes: isset($data['estimated_minutes']) ? (int) $data['estimated_minutes'] : null,
            isActive: (bool) $data['is_active'],
        );
    }
}
