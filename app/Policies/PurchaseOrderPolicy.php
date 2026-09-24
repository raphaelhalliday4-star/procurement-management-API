<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class PurchaseOrderPolicy
{
    use ChecksPermissions;
    public function viewAny(User $user): bool
    {
        return $this->allowed($user, 'purchase_orders.view');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->allowed($user, 'purchase_orders.view');
    }

    public function create(User $user): bool
    {
        return $this->allowed($user, 'purchase_orders.create');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->allowed($user, 'purchase_orders.create')
            && $purchaseOrder->status === 'draft';
    }

    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->allowed($user, 'purchase_orders.approve');
    }

    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->allowed($user, 'purchase_orders.create');
    }
}