<?php

namespace App\Models;

use App\Enums\QuotationItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'quotation_id',
        'item_type',
        'service_catalog_id',
        'part_catalog_id',
        'code',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'line_total',
        'notes',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_type' => QuotationItemType::class,
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Quotation, $this>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * @return BelongsTo<ServiceCatalog, $this>
     */
    public function serviceCatalog(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalog::class, 'service_catalog_id');
    }

    /**
     * @return BelongsTo<PartCatalog, $this>
     */
    public function partCatalog(): BelongsTo
    {
        return $this->belongsTo(PartCatalog::class, 'part_catalog_id');
    }
}
