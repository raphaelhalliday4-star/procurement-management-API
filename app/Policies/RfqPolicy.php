<?php

namespace App\Policies;

use App\Models\Rfq;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class RfqPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'rfqs.view'); }
    public function view(User $user, Rfq $model): bool { return $this->allowed($user, 'rfqs.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'rfqs.create'); }
    public function publish(User $user, Rfq $model): bool { return $this->allowed($user, 'rfqs.publish'); }
    public function close(User $user, Rfq $model): bool { return $this->allowed($user, 'rfqs.close'); }
    public function update(User $user, Rfq $model): bool { return $this->allowed($user, 'rfqs.create'); }
    public function delete(User $user, Rfq $model): bool { return $this->allowed($user, 'rfqs.create'); }
}