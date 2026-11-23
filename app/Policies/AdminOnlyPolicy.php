<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminOnlyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    public function view(User $user, Model $model): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->is_active && $user->isAdmin();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->is_active && $user->isAdmin();
    }
}
