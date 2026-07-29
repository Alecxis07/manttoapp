<?php

namespace App\Models;

use App\Contracts\CatalogConcept;
use App\Enums\PartCatalogType;
use App\Models\Concerns\Auditable;
use Database\Factories\PartCatalogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class PartCatalog extends Model implements CatalogConcept
{
    /** @use HasFactory<PartCatalogFactory> */
    use Auditable;

    use HasFactory;

    /**
     * Domain table name per SDD/specs/07 (singular).
     *
     * @var string
     */
    protected $table = 'part_catalog';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'description',
        'type',
        'service_category_id',
        'base_price',
        'unit_of_measure',
        'is_active',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * RN-CAT-002: base_price is referential only. Documents snapshot unit price
     * on line items; catalog changes do not rewrite history.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PartCatalogType::class,
            'base_price' => 'decimal:2',
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
     * RN-CAT-001: in use when referenced by maintenance parts (or quotation items when present).
     */
    public function isInUse(): bool
    {
        if (Schema::hasTable('maintenance_parts') && Schema::hasColumn('maintenance_parts', 'part_catalog_id')) {
            return $this->newQuery()
                ->getConnection()
                ->table('maintenance_parts')
                ->where('part_catalog_id', $this->id)
                ->exists();
        }

        if (Schema::hasTable('quotation_items') && Schema::hasColumn('quotation_items', 'part_catalog_id')) {
            return $this->newQuery()
                ->getConnection()
                ->table('quotation_items')
                ->where('part_catalog_id', $this->id)
                ->exists();
        }

        return false;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * @param  Builder<PartCatalog>  $query
     * @return Builder<PartCatalog>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
