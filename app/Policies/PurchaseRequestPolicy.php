<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class PurchaseRequestPolicy
{
    use ChecksPermissions;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->allowed($user, 'requisitions.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->allowed($user, 'requisitions.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->allowed($user, 'requisitions.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->allowed($user, 'requisitions.create') && $purchaseRequest->status === 'draft';
    }

      public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->allowed($user, 'requisitions.approve') && $purchaseRequest->status === 'pending';
    }

    public function reject(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->allowed($user, 'requisitions.reject') && $purchaseRequest->status === 'pending';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $this->allowed($user, 'requisitions.create');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return false;
    }
}
