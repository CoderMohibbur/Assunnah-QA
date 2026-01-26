<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class QaPermissionSeeder extends Seeder
{
    public function run(): void
    {
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
            Permission::findOrCreate($p);
        }

        $admin = Role::findOrCreate('Admin');
        $admin->syncPermissions($perms);
    }
}
