<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class UserPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'users.view'); }
    public function view(User $user, User $model): bool { return $this->allowed($user, 'users.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'users.create'); }
    public function update(User $user, User $model): bool { return $this->allowed($user, 'users.update'); }
    public function delete(User $user, User $model): bool { return $this->allowed($user, 'users.delete'); }
}