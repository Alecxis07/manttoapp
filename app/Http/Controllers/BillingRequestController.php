<?php

namespace App\Http\Controllers;

use App\Actions\Billing\CreateBillingRequest;
use App\Actions\Billing\MarkAsProcessed;
use App\Actions\Billing\SubmitForReview;
use App\Actions\Billing\TransitionBillingRequestStatus;
use App\Actions\Billing\UpdateBillingRequest;
use App\Enums\BillingRequestStatus;
use App\Http\Requests\MarkBillingRequestProcessedRequest;
use App\Http\Requests\StoreBillingRequestRequest;
use App\Http\Requests\TransitionBillingRequestRequest;
use App\Http\Requests\UpdateBillingRequestRequest;
use App\Models\BillingRequest;
use App\Models\CustomerFiscalProfile;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BillingRequestController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', BillingRequest::class);

        $billingRequests = BillingRequest::query()
            ->with(['customer:id,name', 'vehicle:id,license_plate'])
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('status')->toString(), function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (BillingRequest $billingRequest): array => $this->summary($billingRequest));

        return Inertia::render('BillingRequests/Index', [
            'billingRequests' => $billingRequests,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statuses' => $this->statusOptions(),
            'can' => [
                'create' => $request->user()?->can('create', BillingRequest::class) ?? false,
            ],
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        $this->authorize('create', BillingRequest::class);

        $prefillOrderId = $request->integer('maintenance_order_id') ?: null;
        $prefillQuotationId = $request->integer('quotation_id') ?: null;

        return Inertia::render('BillingRequests/Create', [
            'orders' => $this->orderOptions(),
            'quotations' => $this->quotationOptions(),
            'paymentMethods' => $this->paymentMethodOptions(),
            'paymentForms' => $this->paymentFormOptions(),
            'prefill' => [
                'maintenance_order_id' => $prefillOrderId,
                'quotation_id' => $prefillQuotationId,
            ],
            'fiscalProfiles' => $this->fiscalProfilesForPrefill($prefillOrderId, $prefillQuotationId),
        ]);
    }

    public function store(
        StoreBillingRequestRequest $request,
        CreateBillingRequest $createBillingRequest,
    ): RedirectResponse {
        $billingRequest = $createBillingRequest->handle($request->toDto(), $request->user());

        return redirect()
            ->route('billing-requests.show', $billingRequest)
            ->with('success', __('Solicitud de facturación creada correctamente.'));
    }

    public function show(BillingRequest $billingRequest): InertiaResponse
    {
        $this->authorize('view', $billingRequest);

        $billingRequest->load([
            'customer:id,name,email,phone',
            'vehicle:id,license_plate,brand,model',
            'maintenanceOrder:id,folio',
            'quotation:id,folio,version',
            'items',
            'statusHistory.user:id,name',
            'requester:id,name',
            'processor:id,name',
        ]);

        return Inertia::render('BillingRequests/Show', [
            'billingRequest' => $this->detail($billingRequest),
            'can' => [
                'update' => request()->user()?->can('update', $billingRequest) ?? false,
                'submit' => request()->user()?->can('submit', $billingRequest) ?? false,
                'transition' => request()->user()?->can('transition', $billingRequest) ?? false,
                'process' => request()->user()?->can('process', $billingRequest) ?? false,
            ],
        ]);
    }

    public function edit(BillingRequest $billingRequest): InertiaResponse
    {
        $this->authorize('update', $billingRequest);

        abort_unless($billingRequest->isEditable(), 422, __('La solicitud no es editable en su estado actual.'));

        $billingRequest->load(['items', 'customer:id,name']);

        return Inertia::render('BillingRequests/Edit', [
            'billingRequest' => $this->detail($billingRequest),
            'fiscalProfiles' => CustomerFiscalProfile::query()
                ->where('customer_id', $billingRequest->customer_id)
                ->orderByDesc('is_default')
                ->orderBy('legal_name')
                ->get(['id', 'legal_name', 'rfc', 'is_default'])
                ->map(fn (CustomerFiscalProfile $profile): array => [
                    'value' => $profile->id,
                    'label' => $profile->legal_name.' ('.$profile->rfc.')'.($profile->is_default ? ' — default' : ''),
                ])
                ->all(),
            'paymentMethods' => $this->paymentMethodOptions(),
            'paymentForms' => $this->paymentFormOptions(),
        ]);
    }

    public function update(
        UpdateBillingRequestRequest $request,
        BillingRequest $billingRequest,
        UpdateBillingRequest $updateBillingRequest,
    ): RedirectResponse {
        $updateBillingRequest->handle($billingRequest, $request->toDto(), $request->user());

        return redirect()
            ->route('billing-requests.show', $billingRequest)
            ->with('success', __('Solicitud actualizada correctamente.'));
    }

    public function submit(
        Request $request,
        BillingRequest $billingRequest,
        SubmitForReview $submitForReview,
    ): RedirectResponse {
        $this->authorize('submit', $billingRequest);

        $submitForReview->handle(
            $billingRequest,
            $request->user(),
            $request->string('notes')->toString() ?: null,
        );

        return redirect()
            ->route('billing-requests.show', $billingRequest)
            ->with('success', __('Solicitud enviada a revisión.'));
    }

    public function transition(
        TransitionBillingRequestRequest $request,
        BillingRequest $billingRequest,
        TransitionBillingRequestStatus $transitionBillingRequestStatus,
    ): RedirectResponse {
        $transitionBillingRequestStatus->handle(
            $billingRequest,
            $request->targetStatus(),
            $request->user(),
            $request->validated('notes'),
        );

        return redirect()
            ->route('billing-requests.show', $billingRequest)
            ->with('success', __('Estado de la solicitud actualizado.'));
    }

    public function process(
        MarkBillingRequestProcessedRequest $request,
        BillingRequest $billingRequest,
        MarkAsProcessed $markAsProcessed,
    ): RedirectResponse {
        $markAsProcessed->handle(
            $billingRequest,
            $request->validated('invoice_reference'),
            $request->user(),
            $request->validated('notes'),
        );

        return redirect()
            ->route('billing-requests.show', $billingRequest)
            ->with('success', __('Solicitud marcada como procesada.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(BillingRequest $billingRequest): array
    {
        return [
            'id' => $billingRequest->id,
            'folio' => $billingRequest->folio,
            'status' => $billingRequest->status->value,
            'status_label' => $billingRequest->status->label(),
            'customer' => $billingRequest->customer?->only(['id', 'name']),
            'vehicle' => $billingRequest->vehicle?->only(['id', 'license_plate']),
            'total' => $billingRequest->total,
            'invoice_reference' => $billingRequest->invoice_reference,
            'created_at' => $billingRequest->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function detail(BillingRequest $billingRequest): array
    {
        return [
            'id' => $billingRequest->id,
            'folio' => $billingRequest->folio,
            'status' => $billingRequest->status->value,
            'status_label' => $billingRequest->status->label(),
            'customer_id' => $billingRequest->customer_id,
            'vehicle_id' => $billingRequest->vehicle_id,
            'customer' => $billingRequest->customer?->only(['id', 'name', 'email', 'phone']),
            'vehicle' => $billingRequest->vehicle?->only(['id', 'license_plate', 'brand', 'model']),
            'maintenance_order' => $billingRequest->maintenanceOrder?->only(['id', 'folio']),
            'quotation' => $billingRequest->quotation
                ? [
                    'id' => $billingRequest->quotation->id,
                    'folio' => $billingRequest->quotation->folio,
                    'version' => $billingRequest->quotation->version,
                ]
                : null,
            'customer_fiscal_profile_id' => $billingRequest->customer_fiscal_profile_id,
            'fiscal_profile_snapshot' => $billingRequest->fiscal_profile_snapshot,
            'has_complete_fiscal_data' => $billingRequest->hasCompleteFiscalData(),
            'payment_method_code' => $billingRequest->payment_method_code,
            'payment_form_code' => $billingRequest->payment_form_code,
            'currency' => $billingRequest->currency,
            'subtotal' => $billingRequest->subtotal,
            'discount_total' => $billingRequest->discount_total,
            'tax_total' => $billingRequest->tax_total,
            'total' => $billingRequest->total,
            'tax_rate' => $billingRequest->tax_rate,
            'invoice_reference' => $billingRequest->invoice_reference,
            'notes' => $billingRequest->notes,
            'is_editable' => $billingRequest->isEditable(),
            'is_immutable' => $billingRequest->isImmutable(),
            'requested_by' => $billingRequest->requester?->only(['id', 'name']),
            'processed_by' => $billingRequest->processor?->only(['id', 'name']),
            'processed_at' => $billingRequest->processed_at?->toIso8601String(),
            'created_at' => $billingRequest->created_at?->toIso8601String(),
            'items' => $billingRequest->items->map(fn ($item): array => [
                'id' => $item->id,
                'item_type' => $item->item_type->value,
                'item_type_label' => $item->item_type->label(),
                'code' => $item->code,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
                'line_total' => $item->line_total,
                'notes' => $item->notes,
            ])->all(),
            'status_history' => $billingRequest->relationLoaded('statusHistory')
                ? $billingRequest->statusHistory->map(fn ($row): array => [
                    'from_status' => $row->from_status?->value,
                    'to_status' => $row->to_status->value,
                    'to_status_label' => $row->to_status->label(),
                    'user' => $row->user?->only(['id', 'name']),
                    'notes' => $row->notes,
                    'created_at' => $row->created_at?->toIso8601String(),
                ])->all()
                : [],
            'allowed_transitions' => collect($billingRequest->status->allowedTransitions())
                ->reject(fn (BillingRequestStatus $status): bool => in_array($status, [
                    BillingRequestStatus::PendingReview,
                    BillingRequestStatus::Processed,
                ], true))
                ->map(fn (BillingRequestStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    protected function orderOptions(): array
    {
        return MaintenanceOrder::query()
            ->with('customer:id,name')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'folio', 'customer_id', 'total'])
            ->map(fn (MaintenanceOrder $order): array => [
                'value' => $order->id,
                'label' => $order->folio.' — '.($order->customer?->name ?? '').' ($'.$order->total.')',
            ])
            ->all();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    protected function quotationOptions(): array
    {
        return Quotation::query()
            ->with('customer:id,name')
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'folio', 'version', 'customer_id', 'total'])
            ->map(fn (Quotation $quotation): array => [
                'value' => $quotation->id,
                'label' => $quotation->folio.' v'.$quotation->version.' — '.($quotation->customer?->name ?? '').' ($'.$quotation->total.')',
            ])
            ->all();
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    protected function fiscalProfilesForPrefill(?int $orderId, ?int $quotationId): array
    {
        $customerId = null;

        if ($orderId) {
            $customerId = MaintenanceOrder::query()->whereKey($orderId)->value('customer_id');
        } elseif ($quotationId) {
            $customerId = Quotation::query()->whereKey($quotationId)->value('customer_id');
        }

        if ($customerId === null) {
            return [];
        }

        return CustomerFiscalProfile::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('is_default')
            ->get(['id', 'legal_name', 'rfc', 'is_default'])
            ->map(fn (CustomerFiscalProfile $profile): array => [
                'value' => $profile->id,
                'label' => $profile->legal_name.' ('.$profile->rfc.')'.($profile->is_default ? ' — default' : ''),
            ])
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function statusOptions(): array
    {
        return collect(BillingRequestStatus::cases())
            ->map(fn (BillingRequestStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function paymentMethodOptions(): array
    {
        return [
            ['value' => 'PUE', 'label' => 'PUE — Pago en una sola exhibición'],
            ['value' => 'PPD', 'label' => 'PPD — Pago en parcialidades o diferido'],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function paymentFormOptions(): array
    {
        return [
            ['value' => '01', 'label' => '01 — Efectivo'],
            ['value' => '03', 'label' => '03 — Transferencia electrónica'],
            ['value' => '04', 'label' => '04 — Tarjeta de crédito'],
            ['value' => '28', 'label' => '28 — Tarjeta de débito'],
            ['value' => '99', 'label' => '99 — Por definir'],
        ];
    }
}
