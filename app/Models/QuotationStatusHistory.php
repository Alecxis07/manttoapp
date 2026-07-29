<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationStatusHistory extends Model
{
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'quotation_status_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'quotation_id',
        'from_status',
        'to_status',
        'user_id',
        'notes',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_status' => QuotationStatus::class,
            'to_status' => QuotationStatus::class,
            'created_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
