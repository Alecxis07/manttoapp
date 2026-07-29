<?php

namespace App\Services;

use App\Enums\BillingRequestStatus;
use App\Enums\MaintenanceOrderStatus;
use App\Enums\QuotationStatus;
use App\Models\BillingRequest;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardKpiService
{
    /**
     * Active order statuses (open workflow).
     *
     * @var list<MaintenanceOrderStatus>
     */
    public const ACTIVE_STATUSES = [
        MaintenanceOrderStatus::Received,
        MaintenanceOrderStatus::Diagnosing,
        MaintenanceOrderStatus::PendingApproval,
        MaintenanceOrderStatus::Approved,
        MaintenanceOrderStatus::InProgress,
    ];

    /**
     * Billing statuses awaiting processing.
     *
     * @var list<BillingRequestStatus>
     */
    public const PENDING_BILLING_STATUSES = [
        BillingRequestStatus::PendingReview,
        BillingRequestStatus::Incomplete,
        BillingRequestStatus::Approved,
    ];

    /**
     * @return array{
     *     period: array{from: string, to: string, label: string},
     *     full_access: bool,
     *     active_orders: int,
     *     completed_this_month: int,
     *     pending_quotations: int,
     *     accepted_quotations: int,
     *     pending_billing: int|null,
     *     period_revenue: string|null,
     *     units_served: int|null,
     *     frequent_vehicles: list<array{id: int, customer_id: int, license_plate: string, brand: string|null, model: string|null, orders_count: int}>
     * }
     */
    public function forUser(User $user, ?Carbon $now = null): array
    {
        $now ??= now();
        $from = $now->copy()->startOfMonth();
        $to = $now->copy()->endOfMonth();
        $fullAccess = $user->can('reports.full');

        $activeOrders = MaintenanceOrder::query()
            ->whereIn('status', array_map(fn (MaintenanceOrderStatus $s) => $s->value, self::ACTIVE_STATUSES))
            ->count();

        $completedThisMonth = MaintenanceOrder::query()
            ->whereIn('status', [
                MaintenanceOrderStatus::Completed->value,
                MaintenanceOrderStatus::Delivered->value,
            ])
            ->whereBetween('completed_at', [$from, $to])
            ->count();

        $pendingQuotations = Quotation::query()
            ->where('status', QuotationStatus::Sent)
            ->count();

        $acceptedQuotations = Quotation::query()
            ->where('status', QuotationStatus::Accepted)
            ->whereBetween('accepted_at', [$from, $to])
            ->count();

        $kpis = [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'label' => $from->translatedFormat('F Y'),
            ],
            'full_access' => $fullAccess,
            'active_orders' => $activeOrders,
            'completed_this_month' => $completedThisMonth,
            'pending_quotations' => $pendingQuotations,
            'accepted_quotations' => $acceptedQuotations,
            'pending_billing' => null,
            'period_revenue' => null,
            'units_served' => null,
            'frequent_vehicles' => [],
        ];

        if (! $fullAccess) {
            return $kpis;
        }

        $kpis['pending_billing'] = BillingRequest::query()
            ->whereIn('status', array_map(fn (BillingRequestStatus $s) => $s->value, self::PENDING_BILLING_STATUSES))
            ->count();

        $kpis['period_revenue'] = number_format((float) MaintenanceOrder::query()
            ->whereIn('status', [
                MaintenanceOrderStatus::Completed->value,
                MaintenanceOrderStatus::Delivered->value,
            ])
            ->whereBetween('completed_at', [$from, $to])
            ->sum('total'), 2, '.', '');

        $kpis['units_served'] = (int) MaintenanceOrder::query()
            ->whereBetween('received_at', [$from, $to])
            ->selectRaw('count(distinct vehicle_id) as aggregate')
            ->value('aggregate');

        $kpis['frequent_vehicles'] = $this->frequentVehicles(limit: 5);

        return $kpis;
    }

    /**
     * @return list<array{id: int, customer_id: int, license_plate: string, brand: string|null, model: string|null, orders_count: int}>
     */
    public function frequentVehicles(int $limit = 5): array
    {
        /** @var Collection<int, Vehicle> $vehicles */
        $vehicles = Vehicle::query()
            ->select(['vehicles.id', 'vehicles.customer_id', 'vehicles.license_plate', 'vehicles.brand', 'vehicles.model'])
            ->withCount('orders')
            ->whereHas('orders')
            ->orderByDesc('orders_count')
            ->limit($limit)
            ->get();

        return $vehicles
            ->map(fn (Vehicle $vehicle): array => [
                'id' => $vehicle->id,
                'customer_id' => $vehicle->customer_id,
                'license_plate' => $vehicle->license_plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'orders_count' => (int) $vehicle->orders_count,
            ])
            ->all();
    }
}
