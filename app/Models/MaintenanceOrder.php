<?php

namespace App\Models;

use App\Enums\MaintenanceOrderStatus;
use App\Enums\MaintenanceOrderType;
use App\Models\Concerns\Auditable;
use App\Services\TotalsCalculator;
use Database\Factories\MaintenanceOrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaintenanceOrder extends Model
{
    /** @use HasFactory<MaintenanceOrderFactory> */
    use Auditable;

    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'folio',
        'customer_id',
        'vehicle_id',
        'quotation_id',
        'type',
        'status',
        'received_at',
        'diagnosing_at',
        'pending_approval_at',
        'approved_at',
        'started_at',
        'completed_at',
        'delivered_at',
        'cancelled_at',
        'mileage',
        'reason',
        'diagnosis',
        'technical_notes',
        'cancellation_reason',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'tax_rate',
        'assigned_user_id',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MaintenanceOrderType::class,
            'status' => MaintenanceOrderStatus::class,
            'received_at' => 'datetime',
            'diagnosing_at' => 'datetime',
            'pending_approval_at' => 'datetime',
            'approved_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'mileage' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_rate' => 'decimal:2',
        ];
    }

    public function hasDiagnosis(): bool
    {
        return filled($this->diagnosis);
    }

    public function hasLineItems(): bool
    {
        return $this->items()->exists() || $this->parts()->exists();
    }

    public function isEditable(): bool
    {
        return ! $this->status->isTerminalImmutable();
    }

    /**
     * Recalculate and persist monetary totals from line items + parts (RN-GEN-002).
     *
     * @return array{subtotal: string, discount_total: string, tax_total: string, total: string, tax_rate: string}
     */
    public function recalculateTotals(?TotalsCalculator $calculator = null): array
    {
        $calculator ??= app(TotalsCalculator::class);

        $this->loadMissing(['items', 'parts']);

        $lines = $this->items
            ->map(fn (MaintenanceOrderItem $item): array => [
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount' => $item->discount,
            ])
            ->concat(
                $this->parts->map(fn (MaintenancePart $part): array => [
                    'quantity' => $part->quantity,
                    'unit_price' => $part->unit_price,
                    'discount' => $part->discount,
                ])
            );

        $totals = $calculator->calculate($lines, $this->tax_rate !== null ? (string) $this->tax_rate : null);

        $this->forceFill([
            'subtotal' => $totals['subtotal'],
            'discount_total' => $totals['discount_total'],
            'tax_total' => $totals['tax_total'],
            'total' => $totals['total'],
            'tax_rate' => $totals['tax_rate'],
        ])->save();

        return $totals;
    }

    /**
     * Timestamp column for a given status transition (RN-GEN-007).
     */
    public function timestampColumnFor(MaintenanceOrderStatus $status): ?string
    {
        return match ($status) {
            MaintenanceOrderStatus::Received => 'received_at',
            MaintenanceOrderStatus::Diagnosing => 'diagnosing_at',
            MaintenanceOrderStatus::PendingApproval => 'pending_approval_at',
            MaintenanceOrderStatus::Approved => 'approved_at',
            MaintenanceOrderStatus::InProgress => 'started_at',
            MaintenanceOrderStatus::Completed => 'completed_at',
            MaintenanceOrderStatus::Delivered => 'delivered_at',
            MaintenanceOrderStatus::Cancelled => 'cancelled_at',
        };
    }

    /**
     * @param  Builder<MaintenanceOrder>  $query
     * @return Builder<MaintenanceOrder>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('folio', 'like', $term)
                ->orWhere('reason', 'like', $term)
                ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $term))
                ->orWhereHas('vehicle', fn (Builder $q) => $q->where('license_plate', 'like', $term)
                    ->orWhere('license_plate_normalized', 'like', $term));
        });
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Vehicle, $this>
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return HasMany<MaintenanceOrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MaintenanceOrderItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<MaintenancePart, $this>
     */
    public function parts(): HasMany
    {
        return $this->hasMany(MaintenancePart::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<MaintenanceStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(MaintenanceStatusHistory::class)->orderBy('created_at');
    }

    /**
     * @return MorphMany<Attachment, $this>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * @return MorphMany<Note, $this>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
