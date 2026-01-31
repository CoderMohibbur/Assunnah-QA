<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Categories
        $this->call(CategorySeeder::class);

        // 2) Permissions + Admin Role (guard fixed)
        $this->call(QaPermissionSeeder::class);

        // 3) Create/Update test user
        $user = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'     => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // 4) Assign Admin role
        if (!$user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }

        // 5) Dummy data
        $this->call(DummyQaSeeder::class);
    }
}
