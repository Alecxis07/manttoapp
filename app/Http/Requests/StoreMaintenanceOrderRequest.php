<?php

namespace App\Http\Requests;

use App\DTOs\MaintenanceOrderData;
use App\Enums\CustomerStatus;
use App\Enums\MaintenanceOrderType;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMaintenanceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MaintenanceOrder::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'received_at' => $this->input('received_at', now()->toDateTimeString()),
            'assigned_user_id' => $this->filled('assigned_user_id') ? $this->input('assigned_user_id') : null,
            'technical_notes' => $this->filled('technical_notes') ? $this->input('technical_notes') : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'type' => ['required', Rule::enum(MaintenanceOrderType::class)],
            'reason' => ['required', 'string', 'max:5000'],
            'mileage' => ['required', 'integer', 'min:0'],
            'received_at' => ['required', 'date'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'technical_notes' => ['nullable', 'string', 'max:5000'],
            // Client-sent totals are ignored (RN-GEN-002); accepted only to prove server recalc.
            'subtotal' => ['nullable', 'numeric'],
            'discount_total' => ['nullable', 'numeric'],
            'tax_total' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $customerId = (int) $this->input('customer_id');
            $vehicleId = (int) $this->input('vehicle_id');

            if (! $customerId || ! $vehicleId) {
                return;
            }

            $customer = Customer::query()->find($customerId);

            if ($customer !== null && $customer->status !== CustomerStatus::Active) {
                $validator->errors()->add('customer_id', __('La orden requiere un cliente activo.'));
            }

            $vehicle = Vehicle::query()->find($vehicleId);

            if ($vehicle !== null && (int) $vehicle->customer_id !== $customerId) {
                $validator->errors()->add('vehicle_id', __('La unidad no pertenece al cliente seleccionado.'));
            }
        });
    }

    public function toDto(): MaintenanceOrderData
    {
        /** @var array{
         *     customer_id: int,
         *     vehicle_id: int,
         *     type: string,
         *     reason: string,
         *     mileage: int,
         *     received_at: string,
         *     assigned_user_id?: int|null,
         *     technical_notes?: string|null,
         *     subtotal?: float|null,
         *     discount_total?: float|null,
         *     tax_total?: float|null,
         *     total?: float|null
         * } $data
         */
        $data = $this->validated();

        return new MaintenanceOrderData(
            customerId: (int) $data['customer_id'],
            vehicleId: (int) $data['vehicle_id'],
            type: MaintenanceOrderType::from($data['type']),
            reason: $data['reason'],
            mileage: (int) $data['mileage'],
            receivedAt: Carbon::parse($data['received_at']),
            assignedUserId: isset($data['assigned_user_id']) ? (int) $data['assigned_user_id'] : null,
            technicalNotes: $data['technical_notes'] ?? null,
            clientSubtotal: isset($data['subtotal']) ? (float) $data['subtotal'] : null,
            clientDiscountTotal: isset($data['discount_total']) ? (float) $data['discount_total'] : null,
            clientTaxTotal: isset($data['tax_total']) ? (float) $data['tax_total'] : null,
            clientTotal: isset($data['total']) ? (float) $data['total'] : null,
        );
    }
}
