<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Luis Zuniga',
            'email' => 'luiszu7779@gmail.com',
            'password' => 'motdepasse',
            'role' => UserRoleEnum::Admin->value,
            'email_verified_at' => now(),
        ]);
    }
}
