<?php

namespace App\Actions\Maintenance;

use App\DTOs\MaintenanceOrderData;
use App\Enums\CustomerStatus;
use App\Enums\MaintenanceOrderStatus;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\MaintenanceStatusHistory;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\TotalsCalculator;
use App\Support\FolioGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrder
{
    public function __construct(
        private FolioGenerator $folioGenerator,
        private TotalsCalculator $totalsCalculator,
    ) {}

    public function handle(MaintenanceOrderData $data, User $actor): MaintenanceOrder
    {
        $customer = Customer::query()->findOrFail($data->customerId);
        $vehicle = Vehicle::query()->findOrFail($data->vehicleId);

        $this->ensureCustomerIsActive($customer);
        $this->ensureVehicleBelongsToCustomer($vehicle, $customer);

        return DB::transaction(function () use ($data, $actor, $customer, $vehicle): MaintenanceOrder {
            $taxRate = $this->totalsCalculator->resolveTaxRate();
            $totals = $this->totalsCalculator->calculate([], $taxRate);

            $order = MaintenanceOrder::query()->create([
                'folio' => $this->folioGenerator->generate('maintenance_order'),
                'customer_id' => $customer->id,
                'vehicle_id' => $vehicle->id,
                'type' => $data->type,
                'status' => MaintenanceOrderStatus::Received,
                'received_at' => $data->receivedAt,
                'mileage' => $data->mileage,
                'reason' => $data->reason,
                'technical_notes' => $data->technicalNotes,
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'total' => $totals['total'],
                'tax_rate' => $totals['tax_rate'],
                'assigned_user_id' => $data->assignedUserId,
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ]);

            MaintenanceStatusHistory::query()->create([
                'maintenance_order_id' => $order->id,
                'from_status' => null,
                'to_status' => MaintenanceOrderStatus::Received,
                'user_id' => $actor->id,
                'notes' => __('Orden creada'),
                'created_at' => now(),
            ]);

            return $order->fresh(['customer', 'vehicle', 'assignee', 'statusHistory']);
        });
    }

    protected function ensureCustomerIsActive(Customer $customer): void
    {
        if ($customer->status !== CustomerStatus::Active) {
            throw ValidationException::withMessages([
                'customer_id' => __('La orden requiere un cliente activo.'),
            ]);
        }
    }

    protected function ensureVehicleBelongsToCustomer(Vehicle $vehicle, Customer $customer): void
    {
        if ((int) $vehicle->customer_id !== (int) $customer->id) {
            throw ValidationException::withMessages([
                'vehicle_id' => __('La unidad no pertenece al cliente seleccionado.'),
            ]);
        }
    }
}
