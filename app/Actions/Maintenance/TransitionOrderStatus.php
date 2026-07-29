<?php

namespace App\Actions\Maintenance;

use App\Enums\MaintenanceOrderStatus;
use App\Models\MaintenanceOrder;
use App\Models\MaintenanceStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionOrderStatus
{
    public function handle(
        MaintenanceOrder $order,
        MaintenanceOrderStatus $to,
        User $actor,
        ?string $notes = null,
        ?string $cancellationReason = null,
    ): MaintenanceOrder {
        $from = $order->status;

        if (! $from->canTransitionTo($to, $actor)) {
            throw ValidationException::withMessages([
                'status' => __('Transición de estado no permitida para su rol.'),
            ]);
        }

        if ($to === MaintenanceOrderStatus::Cancelled && blank($cancellationReason) && blank($notes)) {
            throw ValidationException::withMessages([
                'cancellation_reason' => __('La cancelación requiere un motivo.'),
            ]);
        }

        $this->assertBusinessRules($order, $to);

        return DB::transaction(function () use ($order, $from, $to, $actor, $notes, $cancellationReason): MaintenanceOrder {
            $timestampColumn = $order->timestampColumnFor($to);
            $now = now();

            $attributes = [
                'status' => $to,
                'updated_by' => $actor->id,
            ];

            if ($timestampColumn !== null) {
                $attributes[$timestampColumn] = $now;
            }

            if ($to === MaintenanceOrderStatus::Cancelled) {
                $attributes['cancellation_reason'] = $cancellationReason ?: $notes;
            }

            if ($to === MaintenanceOrderStatus::InProgress && $from === MaintenanceOrderStatus::Delivered) {
                // Reopen path: clear completion/delivery stamps for a new work cycle (RN-GEN-007).
                $attributes['completed_at'] = null;
                $attributes['delivered_at'] = null;
            }

            $order->forceFill($attributes)->save();

            MaintenanceStatusHistory::query()->create([
                'maintenance_order_id' => $order->id,
                'from_status' => $from,
                'to_status' => $to,
                'user_id' => $actor->id,
                'notes' => $notes ?? ($to === MaintenanceOrderStatus::Cancelled ? $cancellationReason : null),
                'created_at' => $now,
            ]);

            return $order->fresh(['statusHistory', 'items', 'parts', 'assignee']);
        });
    }

    protected function assertBusinessRules(MaintenanceOrder $order, MaintenanceOrderStatus $to): void
    {
        // RN-ORD-003: diagnosis required before in_progress.
        if ($to === MaintenanceOrderStatus::InProgress && ! $order->hasDiagnosis()) {
            throw ValidationException::withMessages([
                'diagnosis' => __('Debe registrar el diagnóstico antes de pasar a En progreso.'),
            ]);
        }

        // Completed requires diagnosis + line items + assignee.
        if ($to === MaintenanceOrderStatus::Completed) {
            $errors = [];

            if (! $order->hasDiagnosis()) {
                $errors['diagnosis'] = __('La orden terminada requiere diagnóstico.');
            }

            if (! $order->hasLineItems()) {
                $errors['items'] = __('La orden terminada requiere al menos una partida de servicio o refacción.');
            }

            if ($order->assigned_user_id === null) {
                $errors['assigned_user_id'] = __('La orden terminada requiere un responsable asignado.');
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
        }

        // Delivery requires completed (already enforced by state machine), assert dates RN-GEN-007.
        if ($to === MaintenanceOrderStatus::Delivered) {
            if ($order->status !== MaintenanceOrderStatus::Completed) {
                throw ValidationException::withMessages([
                    'status' => __('La entrega requiere que la orden esté terminada.'),
                ]);
            }

            if ($order->completed_at !== null && $order->completed_at->isFuture()) {
                throw ValidationException::withMessages([
                    'completed_at' => __('La fecha de terminación no puede ser futura respecto a la entrega.'),
                ]);
            }
        }

        $this->assertDateSequence($order, $to);
    }

    /**
     * RN-GEN-007 — business dates must follow a valid sequence.
     */
    protected function assertDateSequence(MaintenanceOrder $order, MaintenanceOrderStatus $to): void
    {
        $sequence = [
            MaintenanceOrderStatus::Received->value => $order->received_at,
            MaintenanceOrderStatus::Diagnosing->value => $order->diagnosing_at,
            MaintenanceOrderStatus::PendingApproval->value => $order->pending_approval_at,
            MaintenanceOrderStatus::Approved->value => $order->approved_at,
            MaintenanceOrderStatus::InProgress->value => $order->started_at,
            MaintenanceOrderStatus::Completed->value => $order->completed_at,
            MaintenanceOrderStatus::Delivered->value => $order->delivered_at,
        ];

        $previous = null;

        foreach ($sequence as $status => $timestamp) {
            if ($timestamp === null) {
                if ($status === $to->value) {
                    // About to set "now" — compare against previous known stamp.
                    break;
                }

                continue;
            }

            if ($previous !== null && $timestamp->lt($previous)) {
                throw ValidationException::withMessages([
                    'status' => __('Las fechas de la orden no siguen una secuencia válida.'),
                ]);
            }

            $previous = $timestamp;
        }
    }
}
