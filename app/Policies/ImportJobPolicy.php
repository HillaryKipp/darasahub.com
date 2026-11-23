<?php

namespace App\Policies;

use App\Models\ImportJob;
use App\Models\User;

class ImportJobPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, ImportJob $job): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ImportJob $job): bool
    {
        return false;
    }

    public function delete(User $user, ImportJob $job): bool
    {
        return $user->isAdmin();
    }
}
