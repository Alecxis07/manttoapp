<?php

namespace App\Actions\Vehicles;

use App\DTOs\VehicleData;
use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateVehicle
{
    public function handle(Vehicle $vehicle, VehicleData $data, User $actor, array $files = []): Vehicle
    {
        $this->ensureMileageNonDecreasing($vehicle, $data->currentMileage);
        $this->ensureCustomerIsActiveIfChanged($vehicle, $data->customerId);
        $this->ensureVehicleTypeIsActive($data->vehicleTypeId, $vehicle->vehicle_type_id);

        return DB::transaction(function () use ($vehicle, $data, $actor, $files): Vehicle {
            $vehicle->update([
                'customer_id' => $data->customerId,
                'vehicle_type_id' => $data->vehicleTypeId,
                'license_plate' => $data->licensePlate,
                'vin' => $data->vin,
                'economic_number' => $data->economicNumber,
                'brand' => $data->brand,
                'model' => $data->model,
                'year' => $data->year,
                'engine_type' => $data->engineType,
                'current_mileage' => $data->currentMileage,
                'status' => $data->status,
                'status_notes' => $data->statusNotes,
                'updated_by' => $actor->id,
            ]);

            $this->storeAttachments($vehicle, $files, $actor);

            return $vehicle->fresh(['customer', 'vehicleType', 'attachments']);
        });
    }

    protected function ensureMileageNonDecreasing(Vehicle $vehicle, int $newMileage): void
    {
        if ($newMileage < $vehicle->current_mileage) {
            throw ValidationException::withMessages([
                'current_mileage' => __('El kilometraje no puede ser menor al registrado actualmente (:mileage km).', [
                    'mileage' => $vehicle->current_mileage,
                ]),
            ]);
        }
    }

    protected function ensureCustomerIsActiveIfChanged(Vehicle $vehicle, int $customerId): void
    {
        if ($customerId === $vehicle->customer_id) {
            return;
        }

        $customer = Customer::query()->findOrFail($customerId);

        if ($customer->status !== CustomerStatus::Active) {
            throw ValidationException::withMessages([
                'customer_id' => __('La unidad debe asociarse a un cliente activo.'),
            ]);
        }
    }

    protected function ensureVehicleTypeIsActive(int $vehicleTypeId, int $currentTypeId): void
    {
        if ($vehicleTypeId === $currentTypeId) {
            return;
        }

        $type = VehicleType::query()->findOrFail($vehicleTypeId);

        if (! $type->is_active) {
            throw ValidationException::withMessages([
                'vehicle_type_id' => __('El tipo de unidad seleccionado no está activo.'),
            ]);
        }
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    protected function storeAttachments(Vehicle $vehicle, array $files, User $actor): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('vehicles/'.$vehicle->id, 'local');

            $vehicle->attachments()->create([
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => (string) $file->getClientMimeType(),
                'size' => $file->getSize() ?: 0,
                'uploaded_by' => $actor->id,
            ]);
        }
    }
}
