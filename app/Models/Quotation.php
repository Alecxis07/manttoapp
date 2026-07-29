<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use App\Models\Concerns\Auditable;
use App\Services\TotalsCalculator;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    /** @use HasFactory<QuotationFactory> */
    use Auditable;

    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'folio',
        'version',
        'parent_quotation_id',
        'customer_id',
        'vehicle_id',
        'status',
        'issued_at',
        'valid_until',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'tax_rate',
        'commercial_terms',
        'accepted_at',
        'accepted_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'cancelled_at',
        'cancellation_reason',
        'maintenance_order_id',
        'created_by',
        'updated_by',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'version' => 1,
        'status' => 'draft',
        'subtotal' => '0.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'total' => '0.00',
        'tax_rate' => '16.00',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'status' => QuotationStatus::class,
            'issued_at' => 'datetime',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    public function isLatestVersion(): bool
    {
        $maxVersion = static::query()
            ->where('folio', $this->folio)
            ->max('version');

        return (int) $this->version === (int) $maxVersion;
    }

    public function isExpiredByDate(): bool
    {
        if ($this->valid_until === null) {
            return false;
        }

        return $this->valid_until->copy()->endOfDay()->isPast();
    }

    public function canBeAccepted(): bool
    {
        if ($this->status === QuotationStatus::Expired || $this->isExpiredByDate()) {
            return false;
        }

        if ($this->status !== QuotationStatus::Sent) {
            return false;
        }

        return $this->isLatestVersion();
    }

    /**
     * @return array{subtotal: string, discount_total: string, tax_total: string, total: string, tax_rate: string}
     */
    public function recalculateTotals(?TotalsCalculator $calculator = null): array
    {
        $calculator ??= app(TotalsCalculator::class);

        $this->loadMissing('items');

        $lines = $this->items->map(fn (QuotationItem $item): array => [
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'discount' => $item->discount,
        ]);

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
     * @param  Builder<Quotation>  $query
     * @return Builder<Quotation>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('folio', 'like', $term)
                ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $term))
                ->orWhereHas('vehicle', fn (Builder $q) => $q->where('license_plate', 'like', $term)
                    ->orWhere('license_plate_normalized', 'like', $term));
        });
    }

    /**
     * @return BelongsTo<Quotation, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_quotation_id');
    }

    /**
     * @return HasMany<Quotation, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_quotation_id');
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
     * @return BelongsTo<MaintenanceOrder, $this>
     */
    public function maintenanceOrder(): BelongsTo
    {
        return $this->belongsTo(MaintenanceOrder::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function acceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
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
     * @return HasMany<QuotationItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<QuotationStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(QuotationStatusHistory::class)->orderBy('created_at');
    }
}
