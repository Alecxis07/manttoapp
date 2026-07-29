<?php

namespace App\Http\Controllers;

use App\Actions\Vehicles\CreateVehicle;
use App\Actions\Vehicles\DeleteVehicle;
use App\Actions\Vehicles\ExportVehicleHistoryPdf;
use App\Actions\Vehicles\UpdateVehicle;
use App\Enums\ExpedienteEventType;
use App\Enums\VehicleStatus;
use App\Http\Requests\FilterVehicleHistoryRequest;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Services\VehicleExpedienteTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class VehicleController extends Controller
{
    /**
     * Global vehicle list with search/filters (RF-UNI-003).
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Vehicle::class);

        $vehicles = Vehicle::query()
            ->with(['customer:id,name,trade_name', 'vehicleType:id,name,code'])
            ->search($request->string('search')->toString() ?: null)
            ->when($request->integer('customer_id'), fn ($query, int $customerId) => $query->where('customer_id', $customerId))
            ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
            ->when($request->string('brand')->toString(), fn ($query, string $brand) => $query->where('brand', 'like', '%'.$brand.'%'))
            ->orderBy('license_plate')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Vehicle $vehicle): array => $this->vehicleSummary($vehicle));

        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'customer_id' => $request->integer('customer_id') ?: null,
                'status' => $request->string('status')->toString(),
                'brand' => $request->string('brand')->toString(),
            ],
            'statuses' => $this->statusOptions(),
            'customers' => Customer::query()->active()->orderBy('name')->limit(100)->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('create', Vehicle::class) ?? false,
            ],
        ]);
    }

    /**
     * Quick plate search insensitive to format (RF-UNI-004).
     */
    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('viewAny', Vehicle::class);

        $plate = $request->string('plate')->toString() ?: $request->string('q')->toString();

        $vehicles = Vehicle::query()
            ->with(['customer:id,name', 'vehicleType:id,name'])
            ->byPlate($plate)
            ->orderBy('license_plate')
            ->limit(15)
            ->get()
            ->map(fn (Vehicle $vehicle): array => [
                'id' => $vehicle->id,
                'customer_id' => $vehicle->customer_id,
                'license_plate' => $vehicle->license_plate,
                'license_plate_normalized' => $vehicle->license_plate_normalized,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'customer_name' => $vehicle->customer?->name,
                'status' => $vehicle->status->value,
                'status_label' => $vehicle->status->label(),
                'url' => route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id]),
            ]);

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json(['data' => $vehicles]);
        }

        if ($vehicles->count() === 1) {
            $match = $vehicles->first();

            return redirect()->to($match['url']);
        }

        return redirect()->route('vehicles.index', [
            'search' => $plate,
        ]);
    }

    public function create(Request $request, ?Customer $customer = null): Response
    {
        $this->authorize('create', Vehicle::class);

        if ($customer !== null) {
            $this->authorize('view', $customer);
        }

        [$yearMin, $yearMax] = Vehicle::yearRange();

        return Inertia::render('Vehicles/Create', [
            'customer' => $customer ? $this->customerOption($customer) : null,
            'customers' => Customer::query()->active()->orderBy('name')->get(['id', 'name', 'trade_name', 'status']),
            'vehicleTypes' => VehicleType::query()->active()->orderBy('name')->get(['id', 'code', 'name']),
            'statuses' => $this->statusOptions(),
            'yearRange' => ['min' => $yearMin, 'max' => $yearMax],
        ]);
    }

    public function store(StoreVehicleRequest $request, CreateVehicle $createVehicle, ?Customer $customer = null): RedirectResponse
    {
        $vehicle = $createVehicle->handle(
            $request->toDto(),
            $request->user(),
            $request->file('attachments', []) ?? []
        );

        return redirect()
            ->route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id])
            ->with('success', __('Unidad registrada correctamente.'));
    }

    public function show(
        FilterVehicleHistoryRequest $request,
        Customer $customer,
        Vehicle $vehicle,
        VehicleExpedienteTimeline $timeline,
    ): Response {
        $this->authorize('view', $vehicle);

        $vehicle->load([
            'customer',
            'vehicleType',
            'attachments.uploader:id,name',
            'creator:id,name',
            'updater:id,name',
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : null;
        $types = $request->typesFilter();

        $history = ActivityLog::query()
            ->where('subject_type', $vehicle->getMorphClass())
            ->where('subject_id', $vehicle->id)
            ->with('user:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'action' => $log->action,
                'properties' => $log->properties,
                'user' => $log->user?->only(['id', 'name']),
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        return Inertia::render('Vehicles/Show', [
            'customer' => $this->customerOption($customer),
            'vehicle' => $this->vehicleDetail($vehicle),
            'attachments' => $vehicle->attachments->map(fn ($attachment): array => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'uploaded_by' => $attachment->uploader?->only(['id', 'name']),
                'created_at' => $attachment->created_at?->toIso8601String(),
            ])->values()->all(),
            'auditHistory' => $history,
            'timeline' => $timeline->build($vehicle, $from, $to, $types),
            'historyFilters' => [
                'from' => $request->string('from')->toString() ?: null,
                'to' => $request->string('to')->toString() ?: null,
                'type' => $request->string('type')->toString() ?: null,
                'types' => $types,
            ],
            'eventTypes' => ExpedienteEventType::options(),
            'can' => [
                'update' => $request->user()?->can('update', $vehicle) ?? false,
                'delete' => $request->user()?->can('delete', $vehicle) ?? false,
                'exportHistory' => $request->user()?->can('exportHistory', $vehicle) ?? false,
            ],
        ]);
    }

    public function exportHistoryPdf(
        FilterVehicleHistoryRequest $request,
        Customer $customer,
        Vehicle $vehicle,
        ExportVehicleHistoryPdf $exportVehicleHistoryPdf,
    ): HttpResponse {
        $this->authorize('exportHistory', $vehicle);

        $from = $request->filled('from') ? Carbon::parse($request->string('from')->toString())->startOfDay() : null;
        $to = $request->filled('to') ? Carbon::parse($request->string('to')->toString())->endOfDay() : null;

        return $exportVehicleHistoryPdf->handle(
            $vehicle,
            $from,
            $to,
            $request->typesFilter(),
        );
    }

    public function edit(Customer $customer, Vehicle $vehicle): Response
    {
        $this->authorize('update', $vehicle);

        $vehicle->load(['customer', 'vehicleType', 'attachments']);
        [$yearMin, $yearMax] = Vehicle::yearRange();

        return Inertia::render('Vehicles/Edit', [
            'customer' => $this->customerOption($customer),
            'vehicle' => $this->vehicleDetail($vehicle),
            'customers' => Customer::query()
                ->where(function ($query) use ($vehicle): void {
                    $query->active()->orWhere('id', $vehicle->customer_id);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'trade_name', 'status']),
            'vehicleTypes' => VehicleType::query()
                ->where(function ($query) use ($vehicle): void {
                    $query->active()->orWhere('id', $vehicle->vehicle_type_id);
                })
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'is_active']),
            'statuses' => $this->statusOptions(),
            'yearRange' => ['min' => $yearMin, 'max' => $yearMax],
            'attachments' => $vehicle->attachments->map(fn ($attachment): array => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
            ])->values()->all(),
        ]);
    }

    public function update(
        UpdateVehicleRequest $request,
        Customer $customer,
        Vehicle $vehicle,
        UpdateVehicle $updateVehicle
    ): RedirectResponse {
        $vehicle = $updateVehicle->handle(
            $vehicle,
            $request->toDto(),
            $request->user(),
            $request->file('attachments', []) ?? []
        );

        return redirect()
            ->route('customers.vehicles.show', [$vehicle->customer_id, $vehicle->id])
            ->with('success', __('Unidad actualizada correctamente.'));
    }

    public function destroy(
        Request $request,
        Customer $customer,
        Vehicle $vehicle,
        DeleteVehicle $deleteVehicle
    ): RedirectResponse {
        $this->authorize('delete', $vehicle);

        $deleteVehicle->handle($vehicle, $request->user());

        return redirect()
            ->route('vehicles.index')
            ->with('success', __('Unidad eliminada correctamente.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function vehicleSummary(Vehicle $vehicle): array
    {
        return [
            'id' => $vehicle->id,
            'customer_id' => $vehicle->customer_id,
            'customer_name' => $vehicle->customer?->name,
            'vehicle_type' => $vehicle->vehicleType?->name,
            'license_plate' => $vehicle->license_plate,
            'license_plate_normalized' => $vehicle->license_plate_normalized,
            'economic_number' => $vehicle->economic_number,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'current_mileage' => $vehicle->current_mileage,
            'status' => $vehicle->status->value,
            'status_label' => $vehicle->status->label(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function vehicleDetail(Vehicle $vehicle): array
    {
        return [
            'id' => $vehicle->id,
            'customer_id' => $vehicle->customer_id,
            'vehicle_type_id' => $vehicle->vehicle_type_id,
            'vehicle_type' => $vehicle->vehicleType?->only(['id', 'code', 'name']),
            'license_plate' => $vehicle->license_plate,
            'license_plate_normalized' => $vehicle->license_plate_normalized,
            'vin' => $vehicle->vin,
            'economic_number' => $vehicle->economic_number,
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'engine_type' => $vehicle->engine_type,
            'current_mileage' => $vehicle->current_mileage,
            'status' => $vehicle->status->value,
            'status_label' => $vehicle->status->label(),
            'status_notes' => $vehicle->status_notes,
            'created_by' => $vehicle->creator?->only(['id', 'name']),
            'updated_by' => $vehicle->updater?->only(['id', 'name']),
            'created_at' => $vehicle->created_at?->toIso8601String(),
            'updated_at' => $vehicle->updated_at?->toIso8601String(),
            'is_in_use' => $vehicle->isInUse(),
        ];
    }

    /**
     * @return array{id: int, name: string, trade_name: string|null, status: string}
     */
    protected function customerOption(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'trade_name' => $customer->trade_name,
            'status' => $customer->status->value,
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return collect(VehicleStatus::cases())
            ->map(fn (VehicleStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }
}
