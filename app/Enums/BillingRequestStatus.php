<?php

namespace App\Enums;

enum BillingRequestStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Incomplete = 'incomplete';
    case Approved = 'approved';
    case Processed = 'processed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::PendingReview, self::Cancelled],
            self::PendingReview => [self::Incomplete, self::Approved, self::Rejected, self::Cancelled],
            self::Incomplete => [self::PendingReview, self::Cancelled],
            self::Approved => [self::Processed, self::Cancelled],
            self::Processed, self::Rejected, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::PendingReview => 'En revisión',
            self::Incomplete => 'Incompleta',
            self::Approved => 'Aprobada',
            self::Processed => 'Procesada',
            self::Rejected => 'Rechazada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function isImmutable(): bool
    {
        return in_array($this, [self::Processed, self::Rejected, self::Cancelled], true);
    }
}
