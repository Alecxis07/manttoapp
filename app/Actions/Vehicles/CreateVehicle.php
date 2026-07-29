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

class CreateVehicle
{
    public function handle(VehicleData $data, User $actor, array $files = []): Vehicle
    {
        $customer = Customer::query()->findOrFail($data->customerId);
        $this->ensureCustomerIsActive($customer);
        $this->ensureVehicleTypeIsActive($data->vehicleTypeId);

        return DB::transaction(function () use ($data, $actor, $files, $customer): Vehicle {
            $vehicle = Vehicle::query()->create([
                'customer_id' => $customer->id,
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
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            $this->storeAttachments($vehicle, $files, $actor);

            return $vehicle->fresh(['customer', 'vehicleType', 'attachments']);
        });
    }

    protected function ensureCustomerIsActive(Customer $customer): void
    {
        if ($customer->status !== CustomerStatus::Active) {
            throw ValidationException::withMessages([
                'customer_id' => __('La unidad debe asociarse a un cliente activo.'),
            ]);
        }
    }

    protected function ensureVehicleTypeIsActive(int $vehicleTypeId): void
    {
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
