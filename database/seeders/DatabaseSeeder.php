<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Database\Seeders\CategorySeeder;
use Database\Seeders\QaPermissionSeeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ 1) Permissions
        $perms = [
            'qa.view_admin',
            'qa.moderate_questions',   // approve/reject
            'qa.write_answers',        // draft
            'qa.publish_answers',      // publish + notify
            'qa.manage_categories',    // optional
            'qa.manage_pages',         // optional
            'qa.manage_settings',      // optional
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // ✅ 2) Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $modRole   = Role::firstOrCreate(['name' => 'Moderator']);

        // Admin gets all
        $adminRole->syncPermissions($perms);


        // Moderator gets limited
        $modRole->syncPermissions([
            'qa.view_admin',
            'qa.moderate_questions',
            'qa.write_answers',
            'qa.publish_answers',
        ]);

        // ✅ 3) Create (or update) test user
        $user = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                // password না দিলে Factory default password সেট হতে পারে না
                // Jetstream/Fortify ব্যবহার করলে লগইনের জন্য password দরকার হবে
                'password' => bcrypt('password'),
            ]
        );

        // ✅ 4) Make this user Admin
        if (!$user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }

        $this->call(CategorySeeder::class);
        $this->call(QaPermissionSeeder::class);
        $this->call([
            DummyQaSeeder::class,
        ]);
    }
}
