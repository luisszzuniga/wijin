<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Formation;
use App\Models\User;

class FormationPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }
}
