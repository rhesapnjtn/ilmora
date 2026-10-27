<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Plan;
use App\Support\DemoCatalog;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DemoCatalog::plans() as $index => $item) {
            $plan = Plan::updateOrCreate(
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

            $courseIds = match ($item['id']) {
                'basic' => Course::query()->where('published', true)->where('level', 'Pemula')->pluck('id')->all(),
                'premium', 'pro' => Course::query()->where('published', true)->pluck('id')->all(),
                default => null,
            };

            if ($courseIds !== null) {
                $plan->courses()->sync($courseIds);
            }
        }
    }
}
