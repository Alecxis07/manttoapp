<?php

namespace App\Models;

use App\Enums\VehicleStatus;
use App\Models\Concerns\Auditable;
use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use Auditable;

    use HasFactory;
    use SoftDeletes;

    public const YEAR_MIN_SETTING_KEY = 'vehicle_year_min';

    public const YEAR_MAX_SETTING_KEY = 'vehicle_year_max';

    public const DEFAULT_YEAR_MIN = 1980;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'vehicle_type_id',
        'license_plate',
        'license_plate_normalized',
        'vin',
        'economic_number',
        'brand',
        'model',
        'year',
        'engine_type',
        'current_mileage',
        'status',
        'status_notes',
        'created_by',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'current_mileage' => 'integer',
            'status' => VehicleStatus::class,
        ];
    }

    /**
     * RN-UNI-001 — normalize plates: uppercase, no spaces/hyphens.
     */
    public static function normalizePlate(string $plate): string
    {
        return strtoupper(str_replace(['-', ' ', '_'], '', trim($plate)));
    }

    /**
     * RN-UNI-002 — normalize VIN for storage/uniqueness.
     */
    public static function normalizeVin(?string $vin): ?string
    {
        if ($vin === null || trim($vin) === '') {
            return null;
        }

        return strtoupper(str_replace(' ', '', trim($vin)));
    }

    /**
     * @return array{0: int, 1: int}
     */
    public static function yearRange(): array
    {
        $min = self::DEFAULT_YEAR_MIN;
        $max = (int) now()->year + 1;

        if (Schema::hasTable('settings')) {
            $minSetting = Setting::query()->where('key', self::YEAR_MIN_SETTING_KEY)->value('value');
            $maxSetting = Setting::query()->where('key', self::YEAR_MAX_SETTING_KEY)->value('value');

            if ($minSetting !== null && $minSetting !== '') {
                $min = (int) $minSetting;
            }

            if ($maxSetting !== null && $maxSetting !== '') {
                $max = (int) $maxSetting;
            }
        }

        return [$min, $max];
    }

    /**
     * Unit with maintenance orders is not deletable (RN-UNI-003).
     */
    public function isInUse(): bool
    {
        if (! Schema::hasTable('maintenance_orders')) {
            return false;
        }

        return $this->orders()->exists();
    }

    /**
     * @return Attribute<string, string>
     */
    protected function licensePlate(): Attribute
    {
        return Attribute::make(
            set: function (string $value): array {
                return [
                    'license_plate' => $value,
                    'license_plate_normalized' => self::normalizePlate($value),
                ];
            },
        );
    }

    /**
     * @return Attribute<?string, ?string>
     */
    protected function vin(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => self::normalizeVin($value),
        );
    }

    /**
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $term = '%'.trim($search).'%';
        $normalized = self::normalizePlate($search);

        return $query->where(function (Builder $query) use ($term, $normalized): void {
            $query->where('license_plate', 'like', $term)
                ->orWhere('license_plate_normalized', 'like', '%'.$normalized.'%')
                ->orWhere('vin', 'like', $term)
                ->orWhere('economic_number', 'like', $term)
                ->orWhere('brand', 'like', $term)
                ->orWhere('model', 'like', $term);
        });
    }

    /**
     * Quick plate search insensitive to format (RF-UNI-004).
     *
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeByPlate(Builder $query, string $plate): Builder
    {
        $normalized = self::normalizePlate($plate);

        if ($normalized === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('license_plate_normalized', 'like', '%'.$normalized.'%');
    }

    /**
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            VehicleStatus::Active->value,
            VehicleStatus::InService->value,
        ]);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<VehicleType, $this>
     */
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    /**
     * @return HasMany<MaintenanceOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(MaintenanceOrder::class);
    }

    /**
     * @return HasMany<Quotation, $this>
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
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
}
