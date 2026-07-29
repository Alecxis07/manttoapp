<?php

namespace App\Http\Requests;

use App\DTOs\VehicleData;
use App\Enums\CustomerStatus;
use App\Enums\VehicleStatus;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Vehicle::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->input('customer_id', $this->route('customer')?->id),
            'vin' => $this->filled('vin') ? $this->input('vin') : null,
            'economic_number' => $this->filled('economic_number') ? trim((string) $this->input('economic_number')) : null,
            'engine_type' => $this->filled('engine_type') ? $this->input('engine_type') : null,
            'status_notes' => $this->filled('status_notes') ? $this->input('status_notes') : null,
            'current_mileage' => $this->input('current_mileage', 0),
        ]);

        if ($this->input('economic_number') === '') {
            $this->merge(['economic_number' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        [$yearMin, $yearMax] = Vehicle::yearRange();
        $customerId = (int) $this->input('customer_id');

        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'vehicle_type_id' => [
                'required',
                'integer',
                Rule::exists('vehicle_types', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'license_plate' => ['required', 'string', 'max:30'],
            'vin' => ['nullable', 'string', 'max:32'],
            'economic_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('vehicles', 'economic_number')
                    ->where(fn ($query) => $query->where('customer_id', $customerId)),
            ],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:'.$yearMin, 'max:'.$yearMax],
            'engine_type' => ['nullable', 'string', 'max:100'],
            'current_mileage' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(VehicleStatus::class)],
            'status_notes' => ['nullable', 'string', 'max:1000'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,webp'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateActiveCustomer($validator);
            $this->validateNormalizedPlateUnique($validator);
            $this->validateNormalizedVinUnique($validator);
        });
    }

    public function toDto(): VehicleData
    {
        /** @var array{
         *     customer_id: int,
         *     vehicle_type_id: int,
         *     license_plate: string,
         *     vin?: string|null,
         *     economic_number?: string|null,
         *     brand: string,
         *     model: string,
         *     year: int,
         *     engine_type?: string|null,
         *     current_mileage?: int|null,
         *     status: string,
         *     status_notes?: string|null
         * } $data
         */
        $data = $this->validated();

        return new VehicleData(
            customerId: (int) $data['customer_id'],
            vehicleTypeId: (int) $data['vehicle_type_id'],
            licensePlate: $data['license_plate'],
            vin: $data['vin'] ?? null,
            economicNumber: $data['economic_number'] ?? null,
            brand: $data['brand'],
            model: $data['model'],
            year: (int) $data['year'],
            engineType: $data['engine_type'] ?? null,
            currentMileage: (int) ($data['current_mileage'] ?? 0),
            status: VehicleStatus::from($data['status']),
            statusNotes: $data['status_notes'] ?? null,
        );
    }

    protected function validateActiveCustomer(Validator $validator): void
    {
        $customerId = $this->input('customer_id');

        if (! $customerId) {
            return;
        }

        $customer = Customer::query()->find($customerId);

        if ($customer === null) {
            return;
        }

        if ($customer->status !== CustomerStatus::Active) {
            $validator->errors()->add('customer_id', __('La unidad debe asociarse a un cliente activo.'));
        }
    }

    protected function validateNormalizedPlateUnique(Validator $validator): void
    {
        $plate = $this->input('license_plate');

        if (! is_string($plate) || $plate === '') {
            return;
        }

        $normalized = Vehicle::normalizePlate($plate);

        $exists = Vehicle::query()
            ->where('license_plate_normalized', $normalized)
            ->exists();

        if ($exists) {
            $validator->errors()->add('license_plate', __('Las placas ya están registradas (tras normalización).'));
        }
    }

    protected function validateNormalizedVinUnique(Validator $validator): void
    {
        $vin = Vehicle::normalizeVin($this->input('vin'));

        if ($vin === null) {
            return;
        }

        $exists = Vehicle::query()
            ->where('vin', $vin)
            ->exists();

        if ($exists) {
            $validator->errors()->add('vin', __('El VIN ya está registrado.'));
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'license_plate.unique' => __('Las placas ya están registradas (tras normalización).'),
            'vin.unique' => __('El VIN ya está registrado.'),
            'economic_number.unique' => __('El número económico ya existe para este cliente.'),
            'year.min' => __('El año está fuera del rango permitido.'),
            'year.max' => __('El año está fuera del rango permitido.'),
        ];
    }
}
