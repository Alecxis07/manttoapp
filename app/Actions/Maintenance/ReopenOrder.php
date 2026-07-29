<?php

namespace App\Actions\Maintenance;

use App\Enums\MaintenanceOrderStatus;
use App\Models\MaintenanceOrder;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReopenOrder
{
    public function __construct(
        private TransitionOrderStatus $transitionOrderStatus,
    ) {}

    public function handle(MaintenanceOrder $order, User $actor, string $reason): MaintenanceOrder
    {
        if ($order->status !== MaintenanceOrderStatus::Delivered) {
            throw ValidationException::withMessages([
                'status' => __('Solo se pueden reabrir órdenes entregadas.'),
            ]);
        }

        if (blank(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => __('La reapertura requiere justificación.'),
            ]);
        }

        if (! $actor->can('maintenance_orders.reopen') && ! $actor->hasRole('admin')) {
            throw ValidationException::withMessages([
                'status' => __('Solo un administrador puede reabrir la orden.'),
            ]);
        }

        return $this->transitionOrderStatus->handle(
            $order,
            MaintenanceOrderStatus::InProgress,
            $actor,
            __('Reapertura: :reason', ['reason' => trim($reason)]),
        );
    }
}
