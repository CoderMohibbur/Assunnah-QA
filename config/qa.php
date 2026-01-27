<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Role Slugs
    |--------------------------------------------------------------------------
    | roles table এ যদি slug থাকে (recommended), এগুলো admin ধরা হবে।
    */
    'admin_role_slugs' => [
        'admin',
        'super_admin',
        'owner',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Role IDs
    |--------------------------------------------------------------------------
    | roles table এ Admin role এর id যদি 1 না হয়, এখানে দিন।
    | উদাহরণ: [1, 2]
    */
    'admin_role_ids' => [
        1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Emails (Fallback)
    |--------------------------------------------------------------------------
    | role/flag না থাকলে নির্দিষ্ট email কে admin ধরা হবে।
    | .env: QA_ADMIN_EMAILS=admin@example.com,owner@example.com
    */
    'admin_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('QA_ADMIN_EMAILS', ''))))),

    /*
    |--------------------------------------------------------------------------
    | Admin Flags (Optional)
    |--------------------------------------------------------------------------
    | users table এ যদি is_admin/is_super_admin/admin টাইপ boolean কলাম থাকে,
    | সেগুলো true হলে admin ধরা হবে।
    */
    'admin_flag_columns' => [
        'is_admin',
        'is_super_admin',
        'admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Role String Columns (Optional)
    |--------------------------------------------------------------------------
    | users table এ যদি role/type/user_role টাইপ string কলাম থাকে,
    | সেখানে admin/super_admin/owner থাকলে admin ধরা হবে।
    */
    'admin_role_string_columns' => [
        'role',
        'type',
        'user_role',
    ],

];
