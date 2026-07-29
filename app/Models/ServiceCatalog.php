<?php

namespace App\Models;

use App\Contracts\CatalogConcept;
use App\Enums\ServiceCatalogType;
use App\Models\Concerns\Auditable;
use Database\Factories\ServiceCatalogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ServiceCatalog extends Model implements CatalogConcept
{
    /** @use HasFactory<ServiceCatalogFactory> */
    use Auditable;

    use HasFactory;

    /**
     * Domain table name per SDD/specs/07 (singular).
     *
     * @var string
     */
    protected $table = 'service_catalog';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'description',
        'service_category_id',
        'type',
        'base_price',
        'unit_of_measure',
        'estimated_minutes',
        'is_active',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * RN-CAT-002: base_price is referential only. Documents (orders/quotations)
     * must snapshot the unit price on line items; changing base_price here does
     * not alter historical documents.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ServiceCatalogType::class,
            'base_price' => 'decimal:2',
            'estimated_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ServiceCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * RN-CAT-001: in use when referenced by order items (or quotation items when present).
     */
    public function isInUse(): bool
    {
        if (Schema::hasTable('maintenance_order_items') && Schema::hasColumn('maintenance_order_items', 'service_catalog_id')) {
            return $this->newQuery()
                ->getConnection()
                ->table('maintenance_order_items')
                ->where('service_catalog_id', $this->id)
                ->exists();
        }

        if (Schema::hasTable('quotation_items') && Schema::hasColumn('quotation_items', 'service_catalog_id')) {
            return $this->newQuery()
                ->getConnection()
                ->table('quotation_items')
                ->where('service_catalog_id', $this->id)
                ->exists();
        }

        return false;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * @param  Builder<ServiceCatalog>  $query
     * @return Builder<ServiceCatalog>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
