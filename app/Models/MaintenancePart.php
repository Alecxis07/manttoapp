<?php

namespace App\Models;

use Database\Factories\MaintenancePartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenancePart extends Model
{
    /** @use HasFactory<MaintenancePartFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'maintenance_order_id',
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
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<MaintenanceOrder, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(MaintenanceOrder::class, 'maintenance_order_id');
    }

    /**
     * @return BelongsTo<PartCatalog, $this>
     */
    public function partCatalog(): BelongsTo
    {
        return $this->belongsTo(PartCatalog::class, 'part_catalog_id');
    }
}
