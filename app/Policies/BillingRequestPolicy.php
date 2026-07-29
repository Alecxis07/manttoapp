<?php

namespace App\Policies;

use App\Models\BillingRequest;
use App\Models\User;

class BillingRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('billing_requests.read');
    }

    public function view(User $user, BillingRequest $billingRequest): bool
    {
        return $user->can('billing_requests.read');
    }

    public function create(User $user): bool
    {
        return $user->can('billing_requests.create');
    }

    public function update(User $user, BillingRequest $billingRequest): bool
    {
        return $user->can('billing_requests.update');
    }

    public function submit(User $user, BillingRequest $billingRequest): bool
    {
        return $user->can('billing_requests.update');
    }

    public function transition(User $user, BillingRequest $billingRequest): bool
    {
        return $user->can('billing_requests.update');
    }

    public function process(User $user, BillingRequest $billingRequest): bool
    {
        return $user->can('billing_requests.update');
    }
}
