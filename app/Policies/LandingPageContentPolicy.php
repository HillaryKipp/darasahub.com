<?php

namespace App\Policies;

use App\Models\LandingPageContent;
use App\Models\User;

class LandingPageContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, LandingPageContent $content): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active && in_array($user->role, [User::ROLE_ADMIN, User::ROLE_REVIEWER, User::ROLE_EDITOR], true);
    }

    public function update(User $user, LandingPageContent $content): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->canReviewAndPublish()) {
            return true;
        }

        return $user->isEditor() && in_array($content->status, [LandingPageContent::STATUS_DRAFT, LandingPageContent::STATUS_REVIEW], true);
    }

    public function approve(User $user, LandingPageContent $content): bool
    {
        return $user->canReviewAndPublish() && ! $content->is_demo;
    }

    public function delete(User $user, LandingPageContent $content): bool
    {
        return $user->isAdmin();
    }
}
