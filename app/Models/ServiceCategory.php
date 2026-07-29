<?php

namespace App\Models;

use App\Contracts\CatalogConcept;
use App\Models\Concerns\Auditable;
use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model implements CatalogConcept
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use Auditable;

    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<ServiceCatalog, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(ServiceCatalog::class);
    }

    /**
     * @return HasMany<PartCatalog, $this>
     */
    public function parts(): HasMany
    {
        return $this->hasMany(PartCatalog::class);
    }

    public function isInUse(): bool
    {
        return $this->services()->exists() || $this->parts()->exists();
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * @param  Builder<ServiceCategory>  $query
     * @return Builder<ServiceCategory>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
