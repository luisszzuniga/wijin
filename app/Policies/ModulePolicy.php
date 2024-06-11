<?php

namespace App\Policies;

use App\Enums\UserRoleEnum;
use App\Models\User;

class ModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }

    public function view(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }

    public function update(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }

    public function delete(User $user): bool
    {
        return $user->role === UserRoleEnum::Admin;
    }
}
