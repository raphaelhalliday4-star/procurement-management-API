<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class PermissionPolicy
{
    use ChecksPermissions;

    public function manage(User $user): bool
    {
      if($user->hasRole('super-admin')){
        return true;
      }
      return false;
    }
}
