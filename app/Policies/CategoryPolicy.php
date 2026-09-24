<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class CategoryPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'services.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'services.create'); }
    public function update(User $user, Category $model): bool { return $this->allowed($user, 'services.update'); }
    public function delete(User $user, Category $model): bool { return $this->allowed($user, 'services.delete'); }
}