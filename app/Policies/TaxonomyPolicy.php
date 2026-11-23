<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TaxonomyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Model $model): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isReviewer();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->isAdmin() || $user->isReviewer();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->isAdmin();
    }
}
