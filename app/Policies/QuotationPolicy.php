<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class QuotationPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool { return $this->allowed($user, 'quotations.view'); }
    public function view(User $user, Quotation $model): bool { return $this->allowed($user, 'quotations.view'); }
    public function create(User $user): bool { return $this->allowed($user, 'quotations.submit'); }
    public function submit(User $user, Quotation $model): bool { return $this->allowed($user, 'quotations.submit'); }
    public function evaluate(User $user, Quotation $model): bool { return $this->allowed($user, 'quotations.evaluate'); }
    public function accept(User $user, Quotation $model): bool { return $this->allowed($user, 'quotations.evaluate'); }
    public function reject(User $user, Quotation $model): bool { return $this->allowed($user, 'quotations.evaluate'); }
    public function update(User $user, Quotation $model): bool { return $this->allowed($user, 'quotations.submit'); }
    public function delete(User $user, Quotation $model): bool { return $this->allowed($user, 'quotations.submit'); }
}