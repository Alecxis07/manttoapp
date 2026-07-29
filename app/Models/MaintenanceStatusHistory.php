<?php

namespace App\Models;

use App\Enums\MaintenanceOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceStatusHistory extends Model
{
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'maintenance_status_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'maintenance_order_id',
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
            'from_status' => MaintenanceOrderStatus::class,
            'to_status' => MaintenanceOrderStatus::class,
            'created_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
