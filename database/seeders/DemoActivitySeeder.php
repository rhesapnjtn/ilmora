<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoActivitySeeder extends Seeder
{
    /**
     * Seed synthetic users, subscriptions, and payments so the admin panel has
     * representative data. Never runs in production.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $courses = Course::query()->published()->get();

        if ($courses->isEmpty()) {
            return;
        }

        $plans = ['Basic', 'Premium', 'Pro'];
        $statuses = ['active', 'active', 'active', 'expired', 'canceled'];
        $paymentStatuses = ['success', 'success', 'success', 'pending', 'failed', 'refunded'];

        User::factory()
            ->count(12)
            ->create()
            ->each(function (User $user) use ($courses, $plans, $statuses, $paymentStatuses): void {
                $start = now()->subDays(random_int(10, 300));
                $status = $statuses[array_rand($statuses)];

                Subscription::create([
                    'user_id' => $user->id,
                    'plan' => $plans[array_rand($plans)],
                    'status' => $status,
                    'starts_at' => $start,
                    'expires_at' => (clone $start)->addMonth(),
                    'amount' => [99000, 149000, 249000][array_rand([99000, 149000, 249000])],
                    'currency' => 'IDR',
                ]);

                foreach ($courses->random(min(3, $courses->count())) as $course) {
                    Payment::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'amount' => $course->price,
                        'currency' => 'IDR',
                        'status' => $paymentStatuses[array_rand($paymentStatuses)],
                        'payment_method' => ['bank_transfer', 'e_wallet', 'credit_card'][array_rand(['bank_transfer', 'e_wallet', 'credit_card'])],
                        'payment_id' => 'DEMO-'.strtoupper(uniqid()),
                    ]);
                }
            });
    }
}
