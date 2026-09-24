<?php

namespace App\Policies;

use App\Models\Rfq_vendor;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class RfqVendorPolicy
{
    use ChecksPermissions;

    public function create(User $user): bool { return $this->allowed($user, 'rfqs.create'); }
    public function delete(User $user, Rfq_vendor $model): bool { return $this->allowed($user, 'rfqs.create'); }
}