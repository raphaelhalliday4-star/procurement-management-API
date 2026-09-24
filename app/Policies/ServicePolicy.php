<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class ServicePolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'services.view'); }
    public function view(User $user, Service $model): bool { return $this->allowed($user, 'services.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'services.create'); }
    public function update(User $user, Service $model): bool { return $this->allowed($user, 'services.update'); }
    public function delete(User $user, Service $model): bool { return $this->allowed($user, 'services.delete'); }
}