<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case Admin = 'admin';
    case User = 'user';
}