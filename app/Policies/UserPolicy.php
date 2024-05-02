<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin->value;
    }
}
