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

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vehicle = $this->route('vehicle');

        return $vehicle instanceof Vehicle
            && ($this->user()?->can('update', $vehicle) ?? false);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->input('customer_id', $this->route('customer')?->id),
            'vin' => $this->filled('vin') ? $this->input('vin') : null,
            'economic_number' => $this->filled('economic_number') ? trim((string) $this->input('economic_number')) : null,
            'engine_type' => $this->filled('engine_type') ? $this->input('engine_type') : null,
            'status_notes' => $this->filled('status_notes') ? $this->input('status_notes') : null,
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
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');
        [$yearMin, $yearMax] = Vehicle::yearRange();
        $customerId = (int) $this->input('customer_id', $vehicle->customer_id);

        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'vehicle_type_id' => [
                'required',
                'integer',
                Rule::exists('vehicle_types', 'id')->where(function ($query) use ($vehicle) {
                    $query->where('is_active', true)
                        ->orWhere('id', $vehicle->vehicle_type_id);
                }),
            ],
            'license_plate' => ['required', 'string', 'max:30'],
            'vin' => ['nullable', 'string', 'max:32'],
            'economic_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('vehicles', 'economic_number')
                    ->where(fn ($query) => $query->where('customer_id', $customerId))
                    ->ignore($vehicle->id),
            ],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:'.$yearMin, 'max:'.$yearMax],
            'engine_type' => ['nullable', 'string', 'max:100'],
            'current_mileage' => ['required', 'integer', 'min:0'],
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
            $this->validateMileageNonDecreasing($validator);
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
         *     current_mileage: int,
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
            currentMileage: (int) $data['current_mileage'],
            status: VehicleStatus::from($data['status']),
            statusNotes: $data['status_notes'] ?? null,
        );
    }

    protected function validateActiveCustomer(Validator $validator): void
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');
        $customerId = (int) $this->input('customer_id', $vehicle->customer_id);

        if ($customerId === $vehicle->customer_id) {
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
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');
        $plate = $this->input('license_plate');

        if (! is_string($plate) || $plate === '') {
            return;
        }

        $normalized = Vehicle::normalizePlate($plate);

        $exists = Vehicle::query()
            ->where('license_plate_normalized', $normalized)
            ->where('id', '!=', $vehicle->id)
            ->exists();

        if ($exists) {
            $validator->errors()->add('license_plate', __('Las placas ya están registradas (tras normalización).'));
        }
    }

    protected function validateNormalizedVinUnique(Validator $validator): void
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');
        $vin = Vehicle::normalizeVin($this->input('vin'));

        if ($vin === null) {
            return;
        }

        $exists = Vehicle::query()
            ->where('vin', $vin)
            ->where('id', '!=', $vehicle->id)
            ->exists();

        if ($exists) {
            $validator->errors()->add('vin', __('El VIN ya está registrado.'));
        }
    }

    protected function validateMileageNonDecreasing(Validator $validator): void
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');
        $mileage = $this->input('current_mileage');

        if (! is_numeric($mileage)) {
            return;
        }

        if ((int) $mileage < $vehicle->current_mileage) {
            $validator->errors()->add(
                'current_mileage',
                __('El kilometraje no puede ser menor al registrado actualmente (:mileage km).', [
                    'mileage' => $vehicle->current_mileage,
                ])
            );
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'economic_number.unique' => __('El número económico ya existe para este cliente.'),
            'year.min' => __('El año está fuera del rango permitido.'),
            'year.max' => __('El año está fuera del rango permitido.'),
        ];
    }
}
