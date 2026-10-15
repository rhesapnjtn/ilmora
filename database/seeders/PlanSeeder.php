<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use App\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed the subscription plans so the public pricing page and the admin
     * panel operate on persisted records.
     */
    public function run(): void
    {
        foreach (DemoCatalog::plans() as $index => $item) {
            Plan::updateOrCreate(
                ['slug' => $item['id']],
                [
                    'name' => $item['name'],
                    'monthly' => $item['monthly'],
                    'annual' => $item['annual'],
                    'highlighted' => $item['highlighted'],
                    'features' => $item['features'],
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );
        }
    }
}
