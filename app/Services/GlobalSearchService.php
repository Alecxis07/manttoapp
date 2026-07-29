<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class GlobalSearchService
{
    public const PER_GROUP_LIMIT = 8;

    /**
     * @return array{
     *     query: string,
     *     customers: list<array{id: int, name: string, trade_name: string|null, email: string|null, url: string}>,
     *     vehicles: list<array{id: int, customer_id: int, license_plate: string, brand: string|null, model: string|null, customer_name: string|null, url: string}>,
     *     orders: list<array{id: int, folio: string, status: string, status_label: string, customer_name: string|null, license_plate: string|null, url: string}>
     * }
     */
    public function search(string $query): array
    {
        $term = trim($query);

        if ($term === '') {
            return [
                'query' => '',
                'customers' => [],
                'vehicles' => [],
                'orders' => [],
            ];
        }

        return [
            'query' => $term,
            'customers' => $this->searchCustomers($term),
            'vehicles' => $this->searchVehicles($term),
            'orders' => $this->searchOrders($term),
        ];
    }

    /**
     * @return list<array{id: int, name: string, trade_name: string|null, email: string|null, url: string}>
     */
    private function searchCustomers(string $term): array
    {
        /** @var Collection<int, Customer> $customers */
        $customers = Customer::query()
            ->search($term)
            ->orderBy('name')
            ->limit(self::PER_GROUP_LIMIT)
            ->get(['id', 'name', 'trade_name', 'email']);

        return $customers
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'name' => $customer->name,
                'trade_name' => $customer->trade_name,
                'email' => $customer->email,
                'url' => route('customers.show', $customer),
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, customer_id: int, license_plate: string, brand: string|null, model: string|null, customer_name: string|null, url: string}>
     */
    private function searchVehicles(string $term): array
    {
        $like = '%'.$term.'%';

        /** @var Collection<int, Vehicle> $vehicles */
        $vehicles = Vehicle::query()
            ->with(['customer:id,name'])
            ->where(function ($query) use ($term, $like): void {
                $query->search($term)
                    ->orWhereHas('customer', function ($customerQuery) use ($like): void {
                        $customerQuery->where('name', 'like', $like)
                            ->orWhere('trade_name', 'like', $like);
                    });
            })
            ->orderBy('license_plate')
            ->limit(self::PER_GROUP_LIMIT)
            ->get(['id', 'customer_id', 'license_plate', 'brand', 'model']);

        return $vehicles
            ->map(fn (Vehicle $vehicle): array => [
                'id' => $vehicle->id,
                'customer_id' => $vehicle->customer_id,
                'license_plate' => $vehicle->license_plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'customer_name' => $vehicle->customer?->name,
                'url' => route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]),
            ])
            ->all();
    }

    /**
     * @return list<array{id: int, folio: string, status: string, status_label: string, customer_name: string|null, license_plate: string|null, url: string}>
     */
    private function searchOrders(string $term): array
    {
        /** @var Collection<int, MaintenanceOrder> $orders */
        $orders = MaintenanceOrder::query()
            ->with([
                'customer:id,name',
                'vehicle:id,license_plate',
            ])
            ->search($term)
            ->latest('received_at')
            ->limit(self::PER_GROUP_LIMIT)
            ->get(['id', 'folio', 'status', 'customer_id', 'vehicle_id', 'received_at']);

        return $orders
            ->map(fn (MaintenanceOrder $order): array => [
                'id' => $order->id,
                'folio' => $order->folio,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'customer_name' => $order->customer?->name,
                'license_plate' => $order->vehicle?->license_plate,
                'url' => route('maintenance-orders.show', $order),
            ])
            ->all();
    }
}
