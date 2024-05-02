<?php

namespace App\Observers;

use App\Mail\UserCreated;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function creating(User $user): void
    {
        $password = $this->generatePassword(12);

        // Send email and hash password
        $user->password = bcrypt($password);

        Mail::to($user->email)->send(new UserCreated($user, $password));
    }

    private function generatePassword(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
