<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\Intervention;
use App\Models\User;

class InterventionPolicy
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

    public function delete(User $user, Intervention $intervention): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }
}
