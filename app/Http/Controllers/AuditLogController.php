<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterAuditLogRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(FilterAuditLogRequest $request): Response
    {
        $filters = $request->filters();

        $logs = ActivityLog::query()
            ->with(['user:id,name,email'])
            ->when($filters['entity'] !== '', function (Builder $query) use ($filters): void {
                $entity = $filters['entity'];

                if (str_contains($entity, '\\')) {
                    $query->where('subject_type', $entity);

                    return;
                }

                $query->where('subject_type', 'like', '%'.$entity.'%');
            })
            ->when($filters['user_id'] !== null, fn (Builder $query) => $query->where('user_id', $filters['user_id']))
            ->when($filters['action'] !== '', fn (Builder $query) => $query->where('action', $filters['action']))
            ->when($filters['from'] !== null, fn (Builder $query) => $query->whereDate('created_at', '>=', $filters['from']))
            ->when($filters['to'] !== null, fn (Builder $query) => $query->whereDate('created_at', '<=', $filters['to']))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'action' => $log->action,
                'subject_type' => $log->subject_type,
                'subject_type_label' => $this->subjectLabel($log->subject_type),
                'subject_id' => $log->subject_id,
                'user' => $log->user === null ? null : [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ],
                'properties' => $log->properties,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at?->toIso8601String(),
            ]);

        $actions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->values()
            ->all();

        $entities = ActivityLog::query()
            ->whereNotNull('subject_type')
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => $this->subjectLabel($type),
            ])
            ->values()
            ->all();

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->values()
            ->all();

        return Inertia::render('Audit/Index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => $actions,
            'entities' => $entities,
            'users' => $users,
        ]);
    }

    private function subjectLabel(?string $subjectType): string
    {
        if ($subjectType === null || $subjectType === '') {
            return '—';
        }

        $basename = class_basename($subjectType);

        return match ($basename) {
            'Setting' => 'Configuración',
            'DocumentSequence' => 'Secuencia documental',
            'User' => 'Usuario',
            'Customer' => 'Cliente',
            'Vehicle' => 'Unidad',
            'MaintenanceOrder' => 'Orden de mantenimiento',
            'Quotation' => 'Cotización',
            'BillingRequest' => 'Solicitud de facturación',
            'ServiceCatalog' => 'Servicio',
            'PartCatalog' => 'Refacción',
            'ServiceCategory' => 'Categoría de servicio',
            default => $basename,
        };
    }
}
