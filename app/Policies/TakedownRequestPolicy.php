<?php

namespace App\Policies;

use App\Models\TakedownRequest;
use App\Models\User;

class TakedownRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canReviewAndPublish();
    }

    public function view(User $user, TakedownRequest $request): bool
    {
        return $user->canReviewAndPublish();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, TakedownRequest $request): bool
    {
        return $user->canReviewAndPublish();
    }

    public function delete(User $user, TakedownRequest $request): bool
    {
        return $user->isAdmin();
    }
}
