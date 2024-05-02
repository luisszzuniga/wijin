<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case Admin = 'admin';
    case Formateur = 'formateur';

    public static function toArray(): array
    {
        return [
            self::Admin->value => 'Admin',
            self::Formateur->value => 'Formateur',
        ];
    }
}