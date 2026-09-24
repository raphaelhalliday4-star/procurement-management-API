<?php

namespace App\Policies;

use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class PurchaseOrderItemPolicy
{
    use ChecksPermissions;

    public function create(User $user): bool { return $this->allowed($user, 'purchase_orders.create'); }
    public function update(User $user, PurchaseOrderItem $model): bool { return $this->allowed($user, 'purchase_orders.create'); }
    public function delete(User $user, PurchaseOrderItem $model): bool { return $this->allowed($user, 'purchase_orders.create'); }
}