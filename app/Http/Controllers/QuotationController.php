<?php

namespace App\Http\Controllers;

use App\Actions\Quotations\AcceptQuotation;
use App\Actions\Quotations\CancelQuotation;
use App\Actions\Quotations\ConvertQuotationToOrder;
use App\Actions\Quotations\CreateQuotation;
use App\Actions\Quotations\ExportQuotationPdf;
use App\Actions\Quotations\RejectQuotation;
use App\Actions\Quotations\SendQuotation;
use App\Actions\Quotations\UpdateQuotation;
use App\Actions\Quotations\VersionQuotation;
use App\Enums\QuotationItemType;
use App\Enums\QuotationStatus;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Customer;
use App\Models\PartCatalog;
use App\Models\Quotation;
use App\Models\ServiceCatalog;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class QuotationController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Quotation::class);

        $quotations = Quotation::query()
            ->with(['customer:id,name', 'vehicle:id,license_plate,brand,model'])
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Quotation $quotation): array => $this->quotationSummary($quotation));

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => $this->statusOptions(),
            'can' => [
                'create' => $request->user()?->can('create', Quotation::class) ?? false,
            ],
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        $this->authorize('create', Quotation::class);

        return Inertia::render('Quotations/Create', [
            'customers' => $this->customerOptions(),
            'itemTypes' => $this->itemTypeOptions(),
            'services' => $this->serviceOptions(),
            'parts' => $this->partOptions(),
            'prefill' => [
                'customer_id' => $request->integer('customer_id') ?: null,
                'vehicle_id' => $request->integer('vehicle_id') ?: null,
            ],
        ]);
    }

    public function store(StoreQuotationRequest $request, CreateQuotation $createQuotation): RedirectResponse
    {
        $quotation = $createQuotation->handle($request->toDto(), $request->user());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', __('Cotización creada correctamente.'));
    }

    public function show(Quotation $quotation): InertiaResponse
    {
        $this->authorize('view', $quotation);

        $quotation->load([
            'customer:id,name,email,phone',
            'vehicle:id,customer_id,license_plate,brand,model,current_mileage',
            'items',
            'statusHistory.user:id,name',
            'creator:id,name',
            'acceptor:id,name',
        ]);

        $versions = Quotation::query()
            ->where('folio', $quotation->folio)
            ->orderByDesc('version')
            ->get(['id', 'folio', 'version', 'status', 'total', 'created_at']);

        return Inertia::render('Quotations/Show', [
            'quotation' => $this->quotationDetail($quotation),
            'versions' => $versions->map(fn (Quotation $version): array => [
                'id' => $version->id,
                'folio' => $version->folio,
                'version' => $version->version,
                'status' => $version->status->value,
                'status_label' => $version->status->label(),
                'total' => $version->total,
                'created_at' => $version->created_at?->toIso8601String(),
                'is_current' => $version->id === $quotation->id,
            ])->all(),
            'can' => [
                'update' => request()->user()?->can('update', $quotation) ?? false,
                'send' => request()->user()?->can('send', $quotation) ?? false,
                'version' => request()->user()?->can('version', $quotation) ?? false,
                'accept' => request()->user()?->can('accept', $quotation) ?? false,
                'reject' => request()->user()?->can('reject', $quotation) ?? false,
                'convert' => request()->user()?->can('convert', $quotation) ?? false,
                'cancel' => request()->user()?->can('cancel', $quotation) ?? false,
                'exportPdf' => request()->user()?->can('exportPdf', $quotation) ?? false,
            ],
        ]);
    }

    public function edit(Quotation $quotation): InertiaResponse
    {
        $this->authorize('update', $quotation);

        abort_unless($quotation->isEditable(), 422, __('La cotización enviada es inmutable; genere una nueva versión.'));

        $quotation->load('items');

        return Inertia::render('Quotations/Edit', [
            'quotation' => $this->quotationDetail($quotation),
            'customers' => $this->customerOptions(),
            'itemTypes' => $this->itemTypeOptions(),
            'services' => $this->serviceOptions(),
            'parts' => $this->partOptions(),
        ]);
    }

    public function update(
        UpdateQuotationRequest $request,
        Quotation $quotation,
        UpdateQuotation $updateQuotation,
    ): RedirectResponse {
        $updateQuotation->handle($quotation, $request->toDto(), $request->user());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', __('Cotización actualizada correctamente.'));
    }

    public function send(Quotation $quotation, SendQuotation $sendQuotation): RedirectResponse
    {
        $this->authorize('send', $quotation);

        $sendQuotation->handle($quotation, request()->user());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', __('Cotización enviada.'));
    }

    public function version(
        Request $request,
        Quotation $quotation,
        VersionQuotation $versionQuotation,
    ): RedirectResponse {
        $this->authorize('version', $quotation);

        $version = $versionQuotation->handle($quotation, null, $request->user());

        return redirect()
            ->route('quotations.edit', $version)
            ->with('success', __('Nueva versión creada como borrador.'));
    }

    public function accept(Quotation $quotation, AcceptQuotation $acceptQuotation): RedirectResponse
    {
        $this->authorize('accept', $quotation);

        $acceptQuotation->handle($quotation, request()->user());

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', __('Cotización aceptada.'));
    }

    public function reject(Request $request, Quotation $quotation, RejectQuotation $rejectQuotation): RedirectResponse
    {
        $this->authorize('reject', $quotation);

        $rejectQuotation->handle($quotation, $request->user(), $request->string('reason')->toString() ?: null);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', __('Cotización rechazada.'));
    }

    public function convert(
        Quotation $quotation,
        ConvertQuotationToOrder $convertQuotationToOrder,
    ): RedirectResponse {
        $this->authorize('convert', $quotation);

        $order = $convertQuotationToOrder->handle($quotation, request()->user());

        return redirect()
            ->route('maintenance-orders.show', $order)
            ->with('success', __('Cotización convertida a orden.'));
    }

    public function cancel(Request $request, Quotation $quotation, CancelQuotation $cancelQuotation): RedirectResponse
    {
        $this->authorize('cancel', $quotation);

        $cancelQuotation->handle($quotation, $request->user(), $request->string('reason')->toString() ?: null);

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', __('Cotización cancelada.'));
    }

    public function pdf(Quotation $quotation, ExportQuotationPdf $exportQuotationPdf): Response
    {
        $this->authorize('exportPdf', $quotation);

        return $exportQuotationPdf->handle($quotation);
    }

    /**
     * Vehicles for a customer (selectors).
     *
     * @return list<array{id: int, license_plate: string, brand: string|null, model: string|null}>
     */
    public function vehicles(Customer $customer): JsonResponse
    {
        $this->authorize('viewAny', Quotation::class);

        $vehicles = Vehicle::query()
            ->where('customer_id', $customer->id)
            ->active()
            ->orderBy('license_plate')
            ->get(['id', 'license_plate', 'brand', 'model']);

        return response()->json(['data' => $vehicles]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function quotationSummary(Quotation $quotation): array
    {
        return [
            'id' => $quotation->id,
            'folio' => $quotation->folio,
            'version' => $quotation->version,
            'status' => $quotation->status->value,
            'status_label' => $quotation->status->label(),
            'customer' => $quotation->customer?->only(['id', 'name']),
            'vehicle' => $quotation->vehicle?->only(['id', 'license_plate', 'brand', 'model']),
            'total' => $quotation->total,
            'valid_until' => $quotation->valid_until?->toDateString(),
            'issued_at' => $quotation->issued_at?->toIso8601String(),
            'created_at' => $quotation->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function quotationDetail(Quotation $quotation): array
    {
        return [
            'id' => $quotation->id,
            'folio' => $quotation->folio,
            'version' => $quotation->version,
            'parent_quotation_id' => $quotation->parent_quotation_id,
            'status' => $quotation->status->value,
            'status_label' => $quotation->status->label(),
            'customer_id' => $quotation->customer_id,
            'vehicle_id' => $quotation->vehicle_id,
            'customer' => $quotation->customer?->only(['id', 'name', 'email', 'phone']),
            'vehicle' => $quotation->vehicle?->only(['id', 'license_plate', 'brand', 'model', 'current_mileage']),
            'issued_at' => $quotation->issued_at?->toIso8601String(),
            'valid_until' => $quotation->valid_until?->toDateString(),
            'subtotal' => $quotation->subtotal,
            'discount_total' => $quotation->discount_total,
            'tax_total' => $quotation->tax_total,
            'total' => $quotation->total,
            'tax_rate' => $quotation->tax_rate,
            'commercial_terms' => $quotation->commercial_terms,
            'accepted_at' => $quotation->accepted_at?->toIso8601String(),
            'accepted_by' => $quotation->acceptor?->only(['id', 'name']),
            'maintenance_order_id' => $quotation->maintenance_order_id,
            'is_editable' => $quotation->isEditable(),
            'is_latest_version' => $quotation->isLatestVersion(),
            'can_be_accepted' => $quotation->canBeAccepted(),
            'created_by' => $quotation->creator?->only(['id', 'name']),
            'items' => $quotation->items->map(fn ($item): array => [
                'id' => $item->id,
                'item_type' => $item->item_type->value,
                'item_type_label' => $item->item_type->label(),
                'service_catalog_id' => $item->service_catalog_id,
                'part_catalog_id' => $item->part_catalog_id,
                'code' => $item->code,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'line_total' => $item->line_total,
                'notes' => $item->notes,
                'sort_order' => $item->sort_order,
            ])->all(),
            'status_history' => $quotation->relationLoaded('statusHistory')
                ? $quotation->statusHistory->map(fn ($row): array => [
                    'from_status' => $row->from_status?->value,
                    'to_status' => $row->to_status->value,
                    'to_status_label' => $row->to_status->label(),
                    'user' => $row->user?->only(['id', 'name']),
                    'notes' => $row->notes,
                    'created_at' => $row->created_at?->toIso8601String(),
                ])->all()
                : [],
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    protected function customerOptions(): array
    {
        return Customer::query()
            ->active()
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name'])
            ->map(fn (Customer $customer): array => [
                'value' => $customer->id,
                'label' => $customer->name,
            ])
            ->all();
    }

    /**
     * @return list<array{value: int, label: string, base_price: string}>
     */
    protected function serviceOptions(): array
    {
        return ServiceCatalog::query()
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'description', 'base_price'])
            ->map(fn (ServiceCatalog $service): array => [
                'value' => $service->id,
                'label' => "{$service->code} — {$service->description}",
                'base_price' => (string) $service->base_price,
                'code' => $service->code,
                'description' => $service->description,
            ])
            ->all();
    }

    /**
     * @return list<array{value: int, label: string, base_price: string}>
     */
    protected function partOptions(): array
    {
        return PartCatalog::query()
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'description', 'base_price'])
            ->map(fn (PartCatalog $part): array => [
                'value' => $part->id,
                'label' => "{$part->code} — {$part->description}",
                'base_price' => (string) $part->base_price,
                'code' => $part->code,
                'description' => $part->description,
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return collect(QuotationStatus::cases())
            ->map(fn (QuotationStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function itemTypeOptions(): array
    {
        return collect(QuotationItemType::cases())
            ->map(fn (QuotationItemType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values()
            ->all();
    }
}
