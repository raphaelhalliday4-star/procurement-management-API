<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor_document;
use App\Policies\Concerns\ChecksPermissions;

class VendorDocumentPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'vendors.view'); }
    public function view(User $user, Vendor_document $model): bool { return $this->allowed($user, 'vendors.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'vendors.create'); }
    public function update(User $user, Vendor_document $model): bool { return $this->allowed($user, 'vendors.update'); }
    public function delete(User $user, Vendor_document $model): bool { return $this->allowed($user, 'vendors.delete'); }
}