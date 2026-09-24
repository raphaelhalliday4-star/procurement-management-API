<?php

namespace App\Policies;

use App\Models\payment;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class PaymentPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'payments.view'); }
    public function view(User $user, payment $model): bool { return $this->allowed($user, 'payments.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'payments.create'); }
}