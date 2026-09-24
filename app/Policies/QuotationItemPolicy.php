<?php

namespace App\Policies;

use App\Models\QuotationItem;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class QuotationItemPolicy
{
    use ChecksPermissions;

    public function create(User $user): bool { return $this->allowed($user, 'quotations.submit'); }
    public function update(User $user, QuotationItem $model): bool { return $this->allowed($user, 'quotations.submit'); }
    public function delete(User $user, QuotationItem $model): bool { return $this->allowed($user, 'quotations.submit'); }
}