<?php

namespace App\Policies;

use App\Models\GoodsReceiptItem;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class GoodsReceiptItemPolicy
{
    use ChecksPermissions;

    public function create(User $user): bool { return $this->allowed($user, 'goods_receipts.create'); }
    public function update(User $user, GoodsReceiptItem $model): bool { return $this->allowed($user, 'goods_receipts.create'); }
    public function delete(User $user, GoodsReceiptItem $model): bool { return $this->allowed($user, 'goods_receipts.create'); }
}