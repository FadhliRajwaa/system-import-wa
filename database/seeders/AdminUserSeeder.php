<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
                'role' => 'admin',
            ]
        );

        // Create sample regular users
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User Demo',
                'password' => bcrypt('password'),
                'is_active' => true,
                'role' => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'User Demo 2',
                'password' => bcrypt('password'),
                'is_active' => true,
                'role' => 'user',
            ]
        );

        User::firstOrCreate(
            ['email' => 'user3@example.com'],
            [
                'name' => 'User Demo 3',
                'password' => bcrypt('password'),
                'is_active' => true,
                'role' => 'user',
            ]
        );

    }
}
