<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (DemoCatalog::categories() as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name']],
            );
        }
    }
}
