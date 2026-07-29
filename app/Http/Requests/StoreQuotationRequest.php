<?php

namespace App\Http\Requests;

use App\DTOs\QuotationData;
use App\Enums\QuotationItemType;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Quotation::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:today'],
            'commercial_terms' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', Rule::enum(QuotationItemType::class)],
            'items.*.service_catalog_id' => ['nullable', 'integer', 'exists:service_catalog,id'],
            'items.*.part_catalog_id' => ['nullable', 'integer', 'exists:part_catalog,id'],
            'items.*.code' => ['nullable', 'string', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateVehicleBelongsToCustomer($validator);
            $this->validateItemCatalogLinks($validator);
        });
    }

    public function toDto(): QuotationData
    {
        /** @var array{
         *     customer_id: int,
         *     vehicle_id: int,
         *     valid_until?: string|null,
         *     commercial_terms?: string|null,
         *     items: list<array<string, mixed>>
         * } $data
         */
        $data = $this->validated();

        $items = collect($data['items'])->map(fn (array $item): array => [
            'item_type' => $item['item_type'],
            'service_catalog_id' => $item['service_catalog_id'] ?? null,
            'part_catalog_id' => $item['part_catalog_id'] ?? null,
            'code' => $item['code'] ?? null,
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'discount' => $item['discount'] ?? 0,
            'notes' => $item['notes'] ?? null,
        ])->values()->all();

        return new QuotationData(
            customerId: (int) $data['customer_id'],
            vehicleId: (int) $data['vehicle_id'],
            validUntil: $data['valid_until'] ?? null,
            commercialTerms: $data['commercial_terms'] ?? null,
            items: $items,
        );
    }

    protected function validateVehicleBelongsToCustomer(Validator $validator): void
    {
        $customerId = $this->input('customer_id');
        $vehicleId = $this->input('vehicle_id');

        if (! $customerId || ! $vehicleId) {
            return;
        }

        $belongs = Vehicle::query()
            ->whereKey($vehicleId)
            ->where('customer_id', $customerId)
            ->exists();

        if (! $belongs) {
            $validator->errors()->add('vehicle_id', __('La unidad no pertenece al cliente seleccionado.'));
        }

        $customerActive = Customer::query()
            ->whereKey($customerId)
            ->active()
            ->exists();

        if (! $customerActive) {
            $validator->errors()->add('customer_id', __('Solo se pueden cotizar clientes activos.'));
        }
    }

    protected function validateItemCatalogLinks(Validator $validator): void
    {
        $items = $this->input('items', []);

        if (! is_array($items)) {
            return;
        }

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $type = $item['item_type'] ?? null;

            if ($type === QuotationItemType::Service->value && empty($item['service_catalog_id'])) {
                $validator->errors()->add("items.{$index}.service_catalog_id", __('Seleccione un servicio del catálogo.'));
            }

            if ($type === QuotationItemType::Part->value && empty($item['part_catalog_id'])) {
                $validator->errors()->add("items.{$index}.part_catalog_id", __('Seleccione una refacción del catálogo.'));
            }
        }
    }
}
