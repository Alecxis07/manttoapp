<?php

namespace App\Models;

use App\Enums\BillingRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingRequestStatusHistory extends Model
{
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'billing_request_status_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'billing_request_id',
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
            'from_status' => BillingRequestStatus::class,
            'to_status' => BillingRequestStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<BillingRequest, $this>
     */
    public function billingRequest(): BelongsTo
    {
        return $this->belongsTo(BillingRequest::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
