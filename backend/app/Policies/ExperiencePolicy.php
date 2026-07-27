<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;

class ExperiencePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Experience $experience): bool
    {
        return $user->id === $experience->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Experience $experience): bool
    {
        return $user->id === $experience->user_id || $user->isAdmin();
    }

    public function delete(User $user, Experience $experience): bool
    {
        return $user->id === $experience->user_id || $user->isAdmin();
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
