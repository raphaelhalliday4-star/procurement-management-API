<?php

namespace App\Policies;

use App\Models\GoodsReceipt;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class GoodsReceiptPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'goods_receipts.view'); }
    public function view(User $user, GoodsReceipt $model): bool { return $this->allowed($user, 'goods_receipts.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'goods_receipts.create'); }
    public function update(User $user, GoodsReceipt $model): bool { return $this->allowed($user, 'goods_receipts.create'); }
    public function delete(User $user, GoodsReceipt $model): bool { return $this->allowed($user, 'goods_receipts.create'); }
}