<?php

namespace App\Http\Controllers;

use App\Actions\Vehicles\ExportVehicleHistoryPdf;
use App\Enums\MaintenanceOrderStatus;
use App\Enums\QuotationStatus;
use App\Http\Requests\FilterOrdersReportRequest;
use App\Http\Requests\FilterQuotationsReportRequest;
use App\Http\Requests\FilterVehicleHistoryReportRequest;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('view-reports');

        return Inertia::render('Reports/Index', [
            'can' => [
                'viewFull' => $request->user()?->can('reports.full') ?? false,
                'export' => $request->user()?->can('export-reports') ?? false,
            ],
        ]);
    }

    public function vehicleHistory(FilterVehicleHistoryReportRequest $request): Response
    {
        $this->authorize('view-reports');

        $vehicleId = $request->integer('vehicle_id') ?: null;
        $vehicle = $vehicleId
            ? Vehicle::query()->with(['customer:id,name', 'vehicleType:id,name'])->find($vehicleId)
            : null;

        $from = $request->date('from');
        $to = $request->date('to');

        $orders = null;

        if ($vehicle !== null) {
            $orders = MaintenanceOrder::query()
                ->with([
                    'customer:id,name',
                    'items:id,maintenance_order_id,code,description,quantity,unit_price,discount,line_total',
                    'parts:id,maintenance_order_id,code,description,quantity,unit_price,discount,line_total',
                    'assignee:id,name',
                ])
                ->where('vehicle_id', $vehicle->id)
                ->when($from, fn ($q) => $q->whereDate('received_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('received_at', '<=', $to))
                ->latest('received_at')
                ->paginate(15)
                ->withQueryString()
                ->through(fn (MaintenanceOrder $order): array => $this->orderReportRow($order, includeTotals: true));
        }

        return Inertia::render('Reports/VehicleHistory', [
            'filters' => [
                'vehicle_id' => $vehicleId,
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'vehicle' => $vehicle === null ? null : [
                'id' => $vehicle->id,
                'customer_id' => $vehicle->customer_id,
                'license_plate' => $vehicle->license_plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'customer' => $vehicle->customer,
                'vehicle_type' => $vehicle->vehicleType,
            ],
            'orders' => $orders,
            'vehicles' => Vehicle::query()
                ->with('customer:id,name')
                ->orderBy('license_plate')
                ->limit(200)
                ->get(['id', 'customer_id', 'license_plate', 'brand', 'model'])
                ->map(fn (Vehicle $v): array => [
                    'id' => $v->id,
                    'label' => trim($v->license_plate.' — '.($v->customer?->name ?? '')),
                ]),
            'can' => [
                'exportPdf' => ($request->user()?->can('export-reports') ?? false) && $vehicle !== null,
            ],
        ]);
    }

    public function exportVehicleHistoryPdf(
        FilterVehicleHistoryReportRequest $request,
        ExportVehicleHistoryPdf $export,
    ): HttpResponse {
        $this->authorize('export-reports');

        $vehicle = Vehicle::query()->findOrFail($request->integer('vehicle_id'));
        $this->authorize('view', $vehicle);

        return $export->handle(
            $vehicle,
            $request->date('from'),
            $request->date('to'),
        );
    }

    public function ordersByPeriod(FilterOrdersReportRequest $request): Response|StreamedResponse
    {
        $this->authorize('view-reports');

        $fullAccess = $request->user()?->can('reports.full') ?? false;
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();
        $dateField = $request->string('date_field')->toString() ?: 'received_at';

        if (! in_array($dateField, ['received_at', 'delivered_at', 'completed_at'], true)) {
            $dateField = 'received_at';
        }

        $query = MaintenanceOrder::query()
            ->with([
                'customer:id,name',
                'vehicle:id,license_plate,brand,model',
                'assignee:id,name',
                'items:id,maintenance_order_id,description',
            ])
            ->when($request->string('status')->toString(), fn ($q, string $status) => $q->where('status', $status))
            ->when($request->integer('customer_id'), fn ($q, int $id) => $q->where('customer_id', $id))
            ->whereDate($dateField, '>=', $from)
            ->whereDate($dateField, '<=', $to)
            ->latest($dateField);

        if ($request->boolean('export')) {
            $this->authorize('export-reports');

            return $this->streamOrdersCsv((clone $query)->get(), $fullAccess);
        }

        $summaryCount = (clone $query)->count();
        $summaryTotal = $fullAccess
            ? number_format((float) (clone $query)->sum('total'), 2, '.', '')
            : null;

        $orders = $query
            ->paginate(20)
            ->withQueryString()
            ->through(fn (MaintenanceOrder $order): array => $this->orderReportRow($order, includeTotals: $fullAccess));

        return Inertia::render('Reports/OrdersByPeriod', [
            'orders' => $orders,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'date_field' => $dateField,
                'status' => $request->string('status')->toString(),
                'customer_id' => $request->integer('customer_id') ?: null,
            ],
            'statuses' => collect(MaintenanceOrderStatus::cases())
                ->map(fn (MaintenanceOrderStatus $s): array => [
                    'value' => $s->value,
                    'label' => $s->label(),
                ])
                ->values()
                ->all(),
            'customers' => Customer::query()->active()->orderBy('name')->limit(100)->get(['id', 'name']),
            'summary' => [
                'count' => $summaryCount,
                'total_amount' => $summaryTotal,
            ],
            'can' => [
                'viewFull' => $fullAccess,
                'export' => $request->user()?->can('export-reports') ?? false,
            ],
        ]);
    }

    public function quotationsByStatus(FilterQuotationsReportRequest $request): Response|StreamedResponse
    {
        $this->authorize('view-reports');

        $fullAccess = $request->user()?->can('reports.full') ?? false;
        $from = $request->date('from');
        $to = $request->date('to');

        $baseQuery = Quotation::query()
            ->with([
                'customer:id,name',
                'vehicle:id,license_plate',
            ])
            ->when($request->string('status')->toString(), fn ($q, string $status) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->latest('created_at');

        if ($request->boolean('export')) {
            $this->authorize('export-reports');

            return $this->streamQuotationsCsv((clone $baseQuery)->get(), $fullAccess);
        }

        $quotations = (clone $baseQuery)
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Quotation $quotation): array => [
                'id' => $quotation->id,
                'folio' => $quotation->folio,
                'version' => $quotation->version,
                'status' => $quotation->status->value,
                'status_label' => $quotation->status->label(),
                'customer' => $quotation->customer,
                'vehicle' => $quotation->vehicle,
                'total' => $fullAccess ? $quotation->total : null,
                'created_at' => optional($quotation->created_at)?->toDateString(),
                'valid_until' => $quotation->valid_until?->toDateString(),
                'converted' => $quotation->maintenance_order_id !== null,
            ]);

        $statusCounts = Quotation::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $accepted = (int) ($statusCounts[QuotationStatus::Accepted->value] ?? 0);
        $totalIssued = (int) $statusCounts->sum();
        $converted = Quotation::query()
            ->whereNotNull('maintenance_order_id')
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->count();

        return Inertia::render('Reports/QuotationsByStatus', [
            'quotations' => $quotations,
            'filters' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => collect(QuotationStatus::cases())
                ->map(fn (QuotationStatus $s): array => [
                    'value' => $s->value,
                    'label' => $s->label(),
                ])
                ->values()
                ->all(),
            'status_counts' => $statusCounts,
            'conversion' => [
                'accepted' => $accepted,
                'converted_to_order' => $converted,
                'rate' => $accepted > 0
                    ? round(($converted / $accepted) * 100, 1)
                    : 0.0,
                'total_issued' => $totalIssued,
            ],
            'can' => [
                'viewFull' => $fullAccess,
                'export' => $request->user()?->can('export-reports') ?? false,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderReportRow(MaintenanceOrder $order, bool $includeTotals): array
    {
        return [
            'id' => $order->id,
            'folio' => $order->folio,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'type' => $order->type->value,
            'customer' => $order->customer,
            'vehicle' => $order->vehicle,
            'assignee' => $order->relationLoaded('assignee') ? $order->assignee : null,
            'services' => $order->relationLoaded('items')
                ? $order->items->pluck('description')->filter()->values()->all()
                : [],
            'parts' => $order->relationLoaded('parts')
                ? $order->parts->map(fn ($part): array => [
                    'description' => $part->description,
                    'quantity' => $part->quantity,
                    'line_total' => $includeTotals ? $part->line_total : null,
                ])->values()->all()
                : [],
            'received_at' => optional($order->received_at)?->toDateString(),
            'completed_at' => optional($order->completed_at)?->toDateString(),
            'delivered_at' => optional($order->delivered_at)?->toDateString(),
            'total' => $includeTotals ? $order->total : null,
        ];
    }

    /**
     * @param  Collection<int, MaintenanceOrder>  $orders
     */
    private function streamOrdersCsv($orders, bool $includeTotals): StreamedResponse
    {
        $filename = 'ordenes-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($orders, $includeTotals): void {
            $handle = fopen('php://output', 'w');
            $headers = ['folio', 'cliente', 'unidad', 'estatus', 'tipo', 'recibida', 'servicios'];
            if ($includeTotals) {
                $headers[] = 'total';
            }
            fputcsv($handle, $headers);

            foreach ($orders as $order) {
                $row = [
                    $order->folio,
                    $order->customer?->name,
                    $order->vehicle?->license_plate,
                    $order->status->value,
                    $order->type->value,
                    optional($order->received_at)?->toDateString(),
                    $order->items->pluck('description')->implode('; '),
                ];
                if ($includeTotals) {
                    $row[] = $order->total;
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  Collection<int, Quotation>  $quotations
     */
    private function streamQuotationsCsv($quotations, bool $includeTotals): StreamedResponse
    {
        $filename = 'cotizaciones-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($quotations, $includeTotals): void {
            $handle = fopen('php://output', 'w');
            $headers = ['folio', 'version', 'cliente', 'unidad', 'estatus', 'fecha'];
            if ($includeTotals) {
                $headers[] = 'total';
            }
            fputcsv($handle, $headers);

            foreach ($quotations as $quotation) {
                $row = [
                    $quotation->folio,
                    $quotation->version,
                    $quotation->customer?->name,
                    $quotation->vehicle?->license_plate,
                    $quotation->status->value,
                    optional($quotation->created_at)?->toDateString(),
                ];
                if ($includeTotals) {
                    $row[] = $quotation->total;
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
