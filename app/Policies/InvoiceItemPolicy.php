<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class InvoiceItemPolicy
{
    use ChecksPermissions;

    public function create(User $user): bool { return $this->allowed($user, 'invoices.create'); }
    public function update(User $user, InvoiceItem $model): bool { return $this->allowed($user, 'invoices.create'); }
    public function delete(User $user, InvoiceItem $model): bool { return $this->allowed($user, 'invoices.create'); }
}