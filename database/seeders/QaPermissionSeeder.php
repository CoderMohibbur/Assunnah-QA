<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class QaPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ important: clear cached permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        $perms = [
            'qa.view_admin',
            'qa.moderate_questions',
            'qa.write_answers',
            'qa.publish_answers',
            'qa.manage_categories',
            'qa.manage_pages',
            'qa.manage_settings',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate([
                'name'       => $p,
                'guard_name' => $guard,
            ]);
        }

        $admin = Role::firstOrCreate([
            'name'       => 'Admin',
            'guard_name' => $guard,
        ]);

        $admin->syncPermissions($perms);
    }
}
