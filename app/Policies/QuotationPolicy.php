<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('quotations.read');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.read');
    }

    public function create(User $user): bool
    {
        return $user->can('quotations.create');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update');
    }

    public function send(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update');
    }

    public function version(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update') || $user->can('quotations.create');
    }

    public function accept(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update');
    }

    public function reject(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update');
    }

    public function convert(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update') && $user->can('maintenance_orders.create');
    }

    public function cancel(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.update') || $user->hasRole('admin');
    }

    public function exportPdf(User $user, Quotation $quotation): bool
    {
        return $user->can('quotations.read');
    }
}
