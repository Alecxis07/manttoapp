<?php

namespace App\Services;

use App\Enums\ExpedienteEventType;
use App\Models\Attachment;
use App\Models\BillingRequest;
use App\Models\MaintenanceOrder;
use App\Models\Quotation;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class VehicleExpedienteTimeline
{
    /**
     * Build a consolidated chronological timeline for a vehicle expediente (RF-HIS-001/002).
     *
     * @param  list<string>|null  $types
     * @return list<array<string, mixed>>
     */
    public function build(
        Vehicle $vehicle,
        ?CarbonInterface $from = null,
        ?CarbonInterface $to = null,
        ?array $types = null,
    ): array {
        $allowedTypes = $this->normalizeTypes($types);

        $entries = collect()
            ->when(
                in_array(ExpedienteEventType::Order, $allowedTypes, true),
                fn (Collection $collection) => $collection->concat($this->orderEntries($vehicle))
            )
            ->when(
                in_array(ExpedienteEventType::Quotation, $allowedTypes, true),
                fn (Collection $collection) => $collection->concat($this->quotationEntries($vehicle))
            )
            ->when(
                in_array(ExpedienteEventType::Billing, $allowedTypes, true),
                fn (Collection $collection) => $collection->concat($this->billingEntries($vehicle))
            )
            ->when(
                in_array(ExpedienteEventType::Evidence, $allowedTypes, true),
                fn (Collection $collection) => $collection->concat($this->evidenceEntries($vehicle))
            )
            ->filter(fn (array $entry): bool => $this->matchesDateRange($entry, $from, $to))
            ->sortByDesc(fn (array $entry): string => $entry['occurred_at'] ?? '')
            ->values()
            ->all();

        return $entries;
    }

    /**
     * @param  list<string>|null  $types
     * @return list<ExpedienteEventType>
     */
    protected function normalizeTypes(?array $types): array
    {
        if ($types === null || $types === []) {
            return ExpedienteEventType::cases();
        }

        return collect($types)
            ->map(fn (string $type): ?ExpedienteEventType => ExpedienteEventType::tryFrom($type))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function orderEntries(Vehicle $vehicle): Collection
    {
        return $vehicle->orders()
            ->with(['assignee:id,name'])
            ->get()
            ->map(function (MaintenanceOrder $order): array {
                $occurredAt = $order->received_at ?? $order->created_at;

                return [
                    'id' => 'order-'.$order->id,
                    'type' => ExpedienteEventType::Order->value,
                    'type_label' => ExpedienteEventType::Order->label(),
                    'title' => $order->folio,
                    'subtitle' => $order->reason,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                    'occurred_at' => $occurredAt?->toIso8601String(),
                    'amount' => $order->total !== null ? (string) $order->total : null,
                    'url' => route('maintenance-orders.show', $order),
                    'meta' => [
                        'mileage' => $order->mileage,
                        'assignee' => $order->assignee?->only(['id', 'name']),
                        'diagnosis' => $order->diagnosis,
                    ],
                ];
            });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function quotationEntries(Vehicle $vehicle): Collection
    {
        if (! Schema::hasTable('quotations')) {
            return collect();
        }

        return $vehicle->quotations()
            ->get()
            ->map(function (Quotation $quotation): array {
                $occurredAt = $quotation->issued_at ?? $quotation->created_at;

                return [
                    'id' => 'quotation-'.$quotation->id,
                    'type' => ExpedienteEventType::Quotation->value,
                    'type_label' => ExpedienteEventType::Quotation->label(),
                    'title' => sprintf('%s v%d', $quotation->folio, $quotation->version),
                    'subtitle' => $quotation->commercial_terms,
                    'status' => $quotation->status->value,
                    'status_label' => $quotation->status->label(),
                    'occurred_at' => $occurredAt?->toIso8601String(),
                    'amount' => $quotation->total !== null ? (string) $quotation->total : null,
                    'url' => route('quotations.show', $quotation),
                    'meta' => [
                        'version' => $quotation->version,
                        'valid_until' => $quotation->valid_until?->toDateString(),
                    ],
                ];
            });
    }

    /**
     * Include billing requests when Fase 8 model/table are available.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function billingEntries(Vehicle $vehicle): Collection
    {
        if (! class_exists(BillingRequest::class) || ! Schema::hasTable('billing_requests')) {
            return collect();
        }

        /** @var class-string<Model> $billingRequestClass */
        $billingRequestClass = BillingRequest::class;

        return $billingRequestClass::query()
            ->where('vehicle_id', $vehicle->id)
            ->get()
            ->map(function ($billing): array {
                $status = $billing->status;
                $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;
                $statusLabel = method_exists($status, 'label') ? $status->label() : $statusValue;
                $occurredAt = $billing->created_at;

                return [
                    'id' => 'billing-'.$billing->id,
                    'type' => ExpedienteEventType::Billing->value,
                    'type_label' => ExpedienteEventType::Billing->label(),
                    'title' => $billing->folio ?? ('FAC-'.$billing->id),
                    'subtitle' => $billing->invoice_reference ?? null,
                    'status' => $statusValue,
                    'status_label' => $statusLabel,
                    'occurred_at' => $occurredAt?->toIso8601String(),
                    'amount' => isset($billing->total) ? (string) $billing->total : null,
                    'url' => Route::has('billing-requests.show')
                        ? route('billing-requests.show', $billing)
                        : null,
                    'meta' => [
                        'invoice_reference' => $billing->invoice_reference ?? null,
                    ],
                ];
            });
    }

    /**
     * Evidences: vehicle attachments + attachments on related maintenance orders.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function evidenceEntries(Vehicle $vehicle): Collection
    {
        if (! Schema::hasTable('attachments')) {
            return collect();
        }

        $orderIds = $vehicle->orders()->pluck('id');

        return Attachment::query()
            ->with('uploader:id,name')
            ->where(function ($query) use ($vehicle, $orderIds): void {
                $query->where(function ($q) use ($vehicle): void {
                    $q->where('attachable_type', $vehicle->getMorphClass())
                        ->where('attachable_id', $vehicle->id);
                })->orWhere(function ($q) use ($orderIds): void {
                    $q->where('attachable_type', (new MaintenanceOrder)->getMorphClass())
                        ->whereIn('attachable_id', $orderIds);
                });
            })
            ->latest()
            ->get()
            ->map(function (Attachment $attachment): array {
                $isOrder = $attachment->attachable_type === (new MaintenanceOrder)->getMorphClass();

                return [
                    'id' => 'evidence-'.$attachment->id,
                    'type' => ExpedienteEventType::Evidence->value,
                    'type_label' => ExpedienteEventType::Evidence->label(),
                    'title' => $attachment->original_name,
                    'subtitle' => $isOrder
                        ? __('Evidencia de orden #:id', ['id' => $attachment->attachable_id])
                        : __('Adjunto de unidad'),
                    'status' => null,
                    'status_label' => null,
                    'occurred_at' => $attachment->created_at?->toIso8601String(),
                    'amount' => null,
                    'url' => null,
                    'meta' => [
                        'mime_type' => $attachment->mime_type,
                        'size' => $attachment->size,
                        'uploaded_by' => $attachment->uploader?->only(['id', 'name']),
                        'attachable_type' => $attachment->attachable_type,
                        'attachable_id' => $attachment->attachable_id,
                    ],
                ];
            });
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    protected function matchesDateRange(array $entry, ?CarbonInterface $from, ?CarbonInterface $to): bool
    {
        if ($from === null && $to === null) {
            return true;
        }

        $occurredAt = isset($entry['occurred_at']) ? Carbon::parse($entry['occurred_at']) : null;

        if ($occurredAt === null) {
            return false;
        }

        if ($from !== null && $occurredAt->lt(Carbon::parse($from)->startOfDay())) {
            return false;
        }

        if ($to !== null && $occurredAt->gt(Carbon::parse($to)->endOfDay())) {
            return false;
        }

        return true;
    }
}
