<?php

namespace App\Policies;

use App\Models\Paper;
use App\Models\User;

class PaperPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, Paper $paper): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && in_array($user->role, [User::ROLE_ADMIN, User::ROLE_REVIEWER, User::ROLE_EDITOR], true);
    }

    public function update(User $user, Paper $paper): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->canReviewAndPublish()) {
            return true;
        }

        // Editors can only edit unpublished (draft/review) papers
        return $user->isEditor() && in_array($paper->publication_status, [Paper::PUB_DRAFT, Paper::PUB_REVIEW], true);
    }

    public function publish(User $user, Paper $paper): bool
    {
        return $user->canReviewAndPublish() && ! $paper->is_demo;
    }

    public function delete(User $user, Paper $paper): bool
    {
        return $user->isAdmin();
    }
}
