<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name_bn' => 'অর্থনৈতিক', 'slug' => 'orthonaitik', 'sort_order' => 1, 'is_active' => 1],
            ['name_bn' => 'আকিদা',     'slug' => 'aqida',      'sort_order' => 2, 'is_active' => 1],
            ['name_bn' => 'আদব আখলাক', 'slug' => 'adob-akhlaq', 'sort_order' => 3, 'is_active' => 1],
            ['name_bn' => 'ইতিহাস',    'slug' => 'itihas',     'sort_order' => 4, 'is_active' => 1],
            ['name_bn' => 'ঈদ-কুরবানী','slug' => 'eid-qurbani', 'sort_order' => 5, 'is_active' => 1],
        ];

        foreach ($items as $row) {
            Category::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
