<?php

namespace App\Models;

use App\Enums\BillingRequestStatus;
use App\Models\Concerns\Auditable;
use App\Services\TotalsCalculator;
use Database\Factories\BillingRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingRequest extends Model
{
    /** @use HasFactory<BillingRequestFactory> */
    use Auditable;

    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'folio',
        'customer_id',
        'vehicle_id',
        'maintenance_order_id',
        'quotation_id',
        'customer_fiscal_profile_id',
        'fiscal_profile_snapshot',
        'status',
        'payment_method_code',
        'payment_form_code',
        'currency',
        'subtotal',
        'discount_total',
        'tax_total',
        'total',
        'tax_rate',
        'invoice_reference',
        'notes',
        'requested_by',
        'processed_by',
        'processed_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'draft',
        'currency' => 'MXN',
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
            'fiscal_profile_snapshot' => 'array',
            'status' => BillingRequestStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [
            BillingRequestStatus::Draft,
            BillingRequestStatus::Incomplete,
        ], true);
    }

    public function isImmutable(): bool
    {
        return $this->status->isImmutable();
    }

    /**
     * Required fiscal fields for review submission (RN-FAC-002).
     *
     * @return list<string>
     */
    public static function requiredFiscalFields(): array
    {
        return [
            'legal_name',
            'rfc',
            'tax_regime_code',
            'cfdi_use_code',
            'postal_code',
        ];
    }

    public function hasCompleteFiscalData(): bool
    {
        $snapshot = $this->fiscal_profile_snapshot ?? [];

        foreach (self::requiredFiscalFields() as $field) {
            $value = $snapshot[$field] ?? null;

            if ($value === null || trim((string) $value) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{subtotal: string, discount_total: string, tax_total: string, total: string, tax_rate: string}
     */
    public function recalculateTotals(?TotalsCalculator $calculator = null): array
    {
        $calculator ??= app(TotalsCalculator::class);

        $this->loadMissing('items');

        $lines = $this->items->map(fn (BillingRequestItem $item): array => [
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
     * @param  Builder<BillingRequest>  $query
     * @return Builder<BillingRequest>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('folio', 'like', $term)
                ->orWhere('invoice_reference', 'like', $term)
                ->orWhereHas('customer', fn (Builder $q) => $q->where('name', 'like', $term));
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
     * @return BelongsTo<MaintenanceOrder, $this>
     */
    public function maintenanceOrder(): BelongsTo
    {
        return $this->belongsTo(MaintenanceOrder::class);
    }

    /**
     * @return BelongsTo<Quotation, $this>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * @return BelongsTo<CustomerFiscalProfile, $this>
     */
    public function fiscalProfile(): BelongsTo
    {
        return $this->belongsTo(CustomerFiscalProfile::class, 'customer_fiscal_profile_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * @return HasMany<BillingRequestItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillingRequestItem::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<BillingRequestStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(BillingRequestStatusHistory::class)->orderBy('created_at');
    }
}
