<?php

namespace App\Http\Controllers;

use App\Actions\Maintenance\AddOrderItem;
use App\Actions\Maintenance\AddOrderPart;
use App\Actions\Maintenance\CreateOrder;
use App\Actions\Maintenance\RegisterDiagnosis;
use App\Actions\Maintenance\ReopenOrder;
use App\Actions\Maintenance\TransitionOrderStatus;
use App\Actions\Maintenance\UploadOrderAttachment;
use App\Enums\MaintenanceOrderStatus;
use App\Enums\MaintenanceOrderType;
use App\Http\Requests\RegisterDiagnosisRequest;
use App\Http\Requests\ReopenMaintenanceOrderRequest;
use App\Http\Requests\StoreMaintenanceOrderAttachmentRequest;
use App\Http\Requests\StoreMaintenanceOrderItemRequest;
use App\Http\Requests\StoreMaintenanceOrderPartRequest;
use App\Http\Requests\StoreMaintenanceOrderRequest;
use App\Http\Requests\TransitionMaintenanceOrderRequest;
use App\Http\Requests\UpdateMaintenanceOrderRequest;
use App\Models\Customer;
use App\Models\MaintenanceOrder;
use App\Models\PartCatalog;
use App\Models\ServiceCatalog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MaintenanceOrder::class);

        $orders = MaintenanceOrder::query()
            ->with([
                'customer:id,name',
                'vehicle:id,license_plate,brand,model',
                'assignee:id,name',
            ])
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), fn ($q, string $status) => $q->where('status', $status))
            ->when($request->integer('customer_id'), fn ($q, int $id) => $q->where('customer_id', $id))
            ->when($request->integer('vehicle_id'), fn ($q, int $id) => $q->where('vehicle_id', $id))
            ->when($request->string('type')->toString(), fn ($q, string $type) => $q->where('type', $type))
            ->latest('received_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (MaintenanceOrder $order): array => $this->orderSummary($order));

        return Inertia::render('MaintenanceOrders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
                'customer_id' => $request->integer('customer_id') ?: null,
                'vehicle_id' => $request->integer('vehicle_id') ?: null,
                'type' => $request->string('type')->toString(),
            ],
            'statuses' => $this->statusOptions(),
            'types' => $this->typeOptions(),
            'customers' => Customer::query()->active()->orderBy('name')->limit(100)->get(['id', 'name']),
            'can' => [
                'create' => $request->user()?->can('create', MaintenanceOrder::class) ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', MaintenanceOrder::class);

        return Inertia::render('MaintenanceOrders/Create', [
            'customers' => Customer::query()->active()->orderBy('name')->get(['id', 'name', 'trade_name']),
            'types' => $this->typeOptions(),
            'technicians' => User::query()->orderBy('name')->limit(100)->get(['id', 'name']),
            'prefill' => [
                'customer_id' => $request->integer('customer_id') ?: null,
                'vehicle_id' => $request->integer('vehicle_id') ?: null,
            ],
            'vehicles' => $request->integer('customer_id')
                ? Vehicle::query()
                    ->where('customer_id', $request->integer('customer_id'))
                    ->orderBy('license_plate')
                    ->get(['id', 'customer_id', 'license_plate', 'brand', 'model'])
                : [],
        ]);
    }

    public function store(StoreMaintenanceOrderRequest $request, CreateOrder $createOrder): RedirectResponse
    {
        $order = $createOrder->handle($request->toDto(), $request->user());

        return redirect()
            ->route('maintenance-orders.show', $order)
            ->with('success', __('Orden :folio creada correctamente.', ['folio' => $order->folio]));
    }

    public function show(MaintenanceOrder $maintenanceOrder): Response
    {
        $this->authorize('view', $maintenanceOrder);

        $maintenanceOrder->load([
            'customer:id,name,trade_name,status',
            'vehicle:id,customer_id,license_plate,brand,model,current_mileage',
            'assignee:id,name',
            'creator:id,name',
            'items.serviceCatalog:id,code,description',
            'parts.partCatalog:id,code,description',
            'statusHistory.user:id,name',
            'attachments.uploader:id,name',
        ]);

        $user = request()->user();

        return Inertia::render('MaintenanceOrders/Show', [
            'order' => $this->orderDetail($maintenanceOrder),
            'items' => $maintenanceOrder->items->map(fn ($item): array => [
                'id' => $item->id,
                'code' => $item->code,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'line_total' => $item->line_total,
                'notes' => $item->notes,
            ])->values()->all(),
            'parts' => $maintenanceOrder->parts->map(fn ($part): array => [
                'id' => $part->id,
                'code' => $part->code,
                'description' => $part->description,
                'quantity' => $part->quantity,
                'unit_price' => $part->unit_price,
                'discount' => $part->discount,
                'line_total' => $part->line_total,
                'notes' => $part->notes,
            ])->values()->all(),
            'statusHistory' => $maintenanceOrder->statusHistory->map(fn ($entry): array => [
                'id' => $entry->id,
                'from_status' => $entry->from_status?->value,
                'from_status_label' => $entry->from_status?->label(),
                'to_status' => $entry->to_status->value,
                'to_status_label' => $entry->to_status->label(),
                'notes' => $entry->notes,
                'user' => $entry->user?->only(['id', 'name']),
                'created_at' => $entry->created_at?->toIso8601String(),
            ])->values()->all(),
            'attachments' => $maintenanceOrder->attachments->map(fn ($attachment): array => [
                'id' => $attachment->id,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'uploaded_by' => $attachment->uploader?->only(['id', 'name']),
                'created_at' => $attachment->created_at?->toIso8601String(),
            ])->values()->all(),
            'services' => ServiceCatalog::query()->active()->orderBy('description')->limit(200)->get(['id', 'code', 'description', 'base_price']),
            'partsCatalog' => PartCatalog::query()->active()->orderBy('description')->limit(200)->get(['id', 'code', 'description', 'base_price']),
            'technicians' => User::query()->orderBy('name')->limit(100)->get(['id', 'name']),
            'statuses' => $this->statusOptions(),
            'allowedTransitions' => collect($maintenanceOrder->status->allowedTransitions())
                ->filter(fn (MaintenanceOrderStatus $status): bool => $maintenanceOrder->status->canTransitionTo($status, $user))
                ->map(fn (MaintenanceOrderStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values()
                ->all(),
            'can' => [
                'update' => $user?->can('update', $maintenanceOrder) ?? false,
                'diagnose' => $user?->can('diagnose', $maintenanceOrder) ?? false,
                'addItems' => $user?->can('addItems', $maintenanceOrder) ?? false,
                'changeStatus' => $user?->can('changeStatus', $maintenanceOrder) ?? false,
                'reopen' => $user?->can('reopen', $maintenanceOrder) ?? false,
                'attach' => $user?->can('attach', $maintenanceOrder) ?? false,
            ],
        ]);
    }

    public function update(
        UpdateMaintenanceOrderRequest $request,
        MaintenanceOrder $maintenanceOrder,
    ): RedirectResponse {
        if (! $maintenanceOrder->isEditable() && ! $request->user()->hasRole('admin')) {
            throw ValidationException::withMessages([
                'order' => __('La orden no puede modificarse en su estado actual.'),
            ]);
        }

        $maintenanceOrder->forceFill([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
        ])->save();

        return redirect()
            ->route('maintenance-orders.show', $maintenanceOrder)
            ->with('success', __('Orden actualizada.'));
    }

    public function transition(
        TransitionMaintenanceOrderRequest $request,
        MaintenanceOrder $maintenanceOrder,
        TransitionOrderStatus $transitionOrderStatus,
    ): RedirectResponse {
        $to = MaintenanceOrderStatus::from($request->validated('status'));

        $transitionOrderStatus->handle(
            $maintenanceOrder,
            $to,
            $request->user(),
            $request->validated('notes'),
            $request->validated('cancellation_reason'),
        );

        return redirect()
            ->route('maintenance-orders.show', $maintenanceOrder)
            ->with('success', __('Estado actualizado a :status.', ['status' => $to->label()]));
    }

    public function diagnose(
        RegisterDiagnosisRequest $request,
        MaintenanceOrder $maintenanceOrder,
        RegisterDiagnosis $registerDiagnosis,
    ): RedirectResponse {
        $registerDiagnosis->handle(
            $maintenanceOrder,
            $request->user(),
            $request->validated('diagnosis'),
            $request->validated('technical_notes'),
            $request->validated('assigned_user_id'),
        );

        return redirect()
            ->route('maintenance-orders.show', $maintenanceOrder)
            ->with('success', __('Diagnóstico registrado.'));
    }

    public function storeItem(
        StoreMaintenanceOrderItemRequest $request,
        MaintenanceOrder $maintenanceOrder,
        AddOrderItem $addOrderItem,
    ): RedirectResponse {
        $data = $request->validated();

        $addOrderItem->handle(
            $maintenanceOrder,
            $request->user(),
            isset($data['service_catalog_id']) ? (int) $data['service_catalog_id'] : null,
            (string) ($data['description'] ?? ''),
            $data['quantity'],
            $data['unit_price'],
            $data['discount'] ?? 0,
            $data['notes'] ?? null,
            $data['code'] ?? null,
        );

        return redirect()
            ->route('maintenance-orders.show', $maintenanceOrder)
            ->with('success', __('Servicio agregado a la orden.'));
    }

    public function storePart(
        StoreMaintenanceOrderPartRequest $request,
        MaintenanceOrder $maintenanceOrder,
        AddOrderPart $addOrderPart,
    ): RedirectResponse {
        $data = $request->validated();

        $addOrderPart->handle(
            $maintenanceOrder,
            $request->user(),
            isset($data['part_catalog_id']) ? (int) $data['part_catalog_id'] : null,
            (string) ($data['description'] ?? ''),
            $data['quantity'],
            $data['unit_price'],
            $data['discount'] ?? 0,
            $data['notes'] ?? null,
            $data['code'] ?? null,
        );

        return redirect()
            ->route('maintenance-orders.show', $maintenanceOrder)
            ->with('success', __('Refacción agregada a la orden.'));
    }

    public function reopen(
        ReopenMaintenanceOrderRequest $request,
        MaintenanceOrder $maintenanceOrder,
        ReopenOrder $reopenOrder,
    ): RedirectResponse {
        $reopenOrder->handle(
            $maintenanceOrder,
            $request->user(),
            $request->validated('reason'),
        );

        return redirect()
            ->route('maintenance-orders.show', $maintenanceOrder)
            ->with('success', __('Orden reabierta.'));
    }

    public function storeAttachment(
        StoreMaintenanceOrderAttachmentRequest $request,
        MaintenanceOrder $maintenanceOrder,
        UploadOrderAttachment $uploadOrderAttachment,
    ): RedirectResponse {
        $uploadOrderAttachment->handle(
            $maintenanceOrder,
            $request->file('file'),
            $request->user(),
        );

        return redirect()
            ->route('maintenance-orders.show', $maintenanceOrder)
            ->with('success', __('Evidencia adjuntada.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function orderSummary(MaintenanceOrder $order): array
    {
        return [
            'id' => $order->id,
            'folio' => $order->folio,
            'customer_name' => $order->customer?->name,
            'vehicle_plate' => $order->vehicle?->license_plate,
            'type' => $order->type->value,
            'type_label' => $order->type->label(),
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'total' => $order->total,
            'assignee' => $order->assignee?->only(['id', 'name']),
            'received_at' => $order->received_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function orderDetail(MaintenanceOrder $order): array
    {
        return [
            'id' => $order->id,
            'folio' => $order->folio,
            'customer' => $order->customer?->only(['id', 'name', 'trade_name', 'status']),
            'vehicle' => $order->vehicle?->only(['id', 'customer_id', 'license_plate', 'brand', 'model', 'current_mileage']),
            'type' => $order->type->value,
            'type_label' => $order->type->label(),
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'received_at' => $order->received_at?->toIso8601String(),
            'started_at' => $order->started_at?->toIso8601String(),
            'completed_at' => $order->completed_at?->toIso8601String(),
            'delivered_at' => $order->delivered_at?->toIso8601String(),
            'mileage' => $order->mileage,
            'reason' => $order->reason,
            'diagnosis' => $order->diagnosis,
            'technical_notes' => $order->technical_notes,
            'cancellation_reason' => $order->cancellation_reason,
            'subtotal' => $order->subtotal,
            'discount_total' => $order->discount_total,
            'tax_total' => $order->tax_total,
            'total' => $order->total,
            'tax_rate' => $order->tax_rate,
            'assigned_user_id' => $order->assigned_user_id,
            'assignee' => $order->assignee?->only(['id', 'name']),
            'created_by' => $order->creator?->only(['id', 'name']),
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return collect(MaintenanceOrderStatus::cases())
            ->map(fn (MaintenanceOrderStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function typeOptions(): array
    {
        return collect(MaintenanceOrderType::cases())
            ->map(fn (MaintenanceOrderType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->all();
    }
}
