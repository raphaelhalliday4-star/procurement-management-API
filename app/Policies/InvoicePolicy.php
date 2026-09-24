<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class InvoicePolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'invoices.view'); }
    public function view(User $user, Invoice $model): bool { return $this->allowed($user, 'invoices.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'invoices.create'); }
    public function update(User $user, Invoice $model): bool { return $this->allowed($user, 'invoices.create'); }
    public function submit(User $user, Invoice $model): bool { return $this->allowed($user, 'invoices.create'); }
    public function approve(User $user, Invoice $model): bool { return $this->allowed($user, 'invoices.approve'); }
    public function reject(User $user, Invoice $model): bool { return $this->allowed($user, 'invoices.approve'); }
    public function delete(User $user, Invoice $model): bool { return $this->allowed($user, 'invoices.create'); }
}