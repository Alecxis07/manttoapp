<?php

namespace App\Enums;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

enum MaintenanceOrderStatus: string
{
    case Received = 'received';
    case Diagnosing = 'diagnosing';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Received => [self::Diagnosing, self::Cancelled],
            self::Diagnosing => [self::PendingApproval, self::Cancelled],
            self::PendingApproval => [self::Approved, self::Cancelled],
            self::Approved => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Completed, self::Cancelled],
            self::Completed => [self::Delivered, self::Cancelled],
            self::Delivered => [self::InProgress, self::Cancelled],
            self::Cancelled => [],
        };
    }

    /**
     * Structural transition check. When $user is provided, also enforces role matrix (spec 03 §4.2).
     */
    public function canTransitionTo(self $to, ?User $user = null): bool
    {
        if (! in_array($to, $this->allowedTransitions(), true)) {
            return false;
        }

        if ($user === null) {
            return true;
        }

        return $this->isAllowedForUser($to, $user);
    }

    public function isAllowedForUser(self $to, User $user): bool
    {
        $allowedRoles = $this->rolesAllowedFor($to);

        if ($allowedRoles === []) {
            return false;
        }

        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Role matrix from SDD/specs/03-actors-permissions.md §4.2.
     * Reopen is delivered → in_progress (plan / RF-ORD-008), not received.
     *
     * @return list<string>
     */
    public function rolesAllowedFor(self $to): array
    {
        if ($to === self::Cancelled) {
            return [RolesAndPermissionsSeeder::ROLE_ADMIN];
        }

        return match ([$this, $to]) {
            [self::Received, self::Diagnosing] => [
                RolesAndPermissionsSeeder::ROLE_ADMIN,
                RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
                RolesAndPermissionsSeeder::ROLE_TECNICO,
            ],
            [self::Diagnosing, self::PendingApproval] => [
                RolesAndPermissionsSeeder::ROLE_ADMIN,
                RolesAndPermissionsSeeder::ROLE_TECNICO,
            ],
            [self::PendingApproval, self::Approved] => [
                RolesAndPermissionsSeeder::ROLE_ADMIN,
                RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
            ],
            [self::Approved, self::InProgress] => [
                RolesAndPermissionsSeeder::ROLE_ADMIN,
                RolesAndPermissionsSeeder::ROLE_TECNICO,
            ],
            [self::InProgress, self::Completed] => [
                RolesAndPermissionsSeeder::ROLE_ADMIN,
                RolesAndPermissionsSeeder::ROLE_TECNICO,
            ],
            [self::Completed, self::Delivered] => [
                RolesAndPermissionsSeeder::ROLE_ADMIN,
                RolesAndPermissionsSeeder::ROLE_ADMINISTRATIVO,
            ],
            [self::Delivered, self::InProgress] => [
                RolesAndPermissionsSeeder::ROLE_ADMIN,
            ],
            default => [],
        };
    }

    public function isTerminalImmutable(): bool
    {
        return in_array($this, [self::Completed, self::Delivered, self::Cancelled], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Recibida',
            self::Diagnosing => 'En diagnóstico',
            self::PendingApproval => 'Pendiente de aprobación',
            self::Approved => 'Aprobada',
            self::InProgress => 'En progreso',
            self::Completed => 'Terminada',
            self::Delivered => 'Entregada',
            self::Cancelled => 'Cancelada',
        };
    }
}
