<?php

namespace App\Actions\Maintenance;

use App\Models\MaintenanceOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterDiagnosis
{
    public function handle(
        MaintenanceOrder $order,
        User $actor,
        string $diagnosis,
        ?string $technicalNotes = null,
        ?int $assignedUserId = null,
    ): MaintenanceOrder {
        // RN-ORD-003: diagnosis editable while not yet in progress / terminal.
        if (in_array($order->status->value, ['in_progress', 'completed', 'delivered', 'cancelled'], true)) {
            throw ValidationException::withMessages([
                'diagnosis' => __('El diagnóstico no puede editarse en el estado actual.'),
            ]);
        }

        if (blank(trim($diagnosis))) {
            throw ValidationException::withMessages([
                'diagnosis' => __('El diagnóstico es obligatorio.'),
            ]);
        }

        return DB::transaction(function () use ($order, $actor, $diagnosis, $technicalNotes, $assignedUserId): MaintenanceOrder {
            $attributes = [
                'diagnosis' => trim($diagnosis),
                'updated_by' => $actor->id,
            ];

            if ($technicalNotes !== null) {
                $attributes['technical_notes'] = $technicalNotes;
            }

            if ($assignedUserId !== null) {
                $attributes['assigned_user_id'] = $assignedUserId;
            }

            $order->forceFill($attributes)->save();

            return $order->fresh(['assignee']);
        });
    }
}
