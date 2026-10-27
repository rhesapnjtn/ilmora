<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    /**
     * Badge catalog: code => [name, description, icon].
     *
     * @return array<string, array{name:string, description:string, icon:string}>
     */
    public static function catalog(): array
    {
        return [
            'first_checkin' => ['name' => 'Langkah Pertama', 'description' => 'Check-in pertama kali.', 'icon' => '🌱'],
            'streak_7' => ['name' => 'Semangat 7 Hari', 'description' => 'Streak 7 hari berturut-turut.', 'icon' => '🔥'],
            'streak_30' => ['name' => 'Konsisten Sebulan', 'description' => 'Streak 30 hari berturut-turut.', 'icon' => '🏆'],
            'streak_100' => ['name' => 'Legenda 100 Hari', 'description' => 'Streak 100 hari berturut-turut.', 'icon' => '👑'],
            'first_course' => ['name' => 'Penyelesai Pertama', 'description' => 'Menyelesaikan kursus pertama.', 'icon' => '🎓'],
            'five_courses' => ['name' => 'Pembelajar Sejati', 'description' => 'Menyelesaikan 5 kursus.', 'icon' => '📚'],
            'quiz_master' => ['name' => 'Master Quiz', 'description' => 'Lulus 5 quiz.', 'icon' => '🧠'],
            'point_collector' => ['name' => 'Kolektor Poin', 'description' => 'Mencapai 1000 poin.', 'icon' => '💎'],
            'redeemer' => ['name' => 'Penukar Poin', 'description' => 'Menukar poin untuk kursus pertama.', 'icon' => '🎁'],
        ];
    }

    /**
     * Evaluate and unlock any newly earned badges for the user.
     *
     * @return list<string> newly unlocked badge codes
     */
    public function evaluate(User $user): array
    {
        $unlocked = DB::table('user_badges')
            ->where('user_id', $user->id)
            ->pluck('badge_code')
            ->all();
        $newly = [];

        $checks = [
            'first_checkin' => fn () => $user->pointTransactions()->where('type', 'checkin')->exists(),
            'streak_7' => fn () => (int) $user->current_streak >= 7,
            'streak_30' => fn () => (int) $user->current_streak >= 30,
            'streak_100' => fn () => (int) $user->current_streak >= 100,
            'first_course' => fn () => $user->enrollments()->whereNotNull('completed_at')->count() >= 1,
            'five_courses' => fn () => $user->enrollments()->whereNotNull('completed_at')->count() >= 5,
            'quiz_master' => fn () => QuizAttempt::query()->where('user_id', $user->id)->where('passed', true)->count() >= 5,
            'point_collector' => fn () => (int) $user->pointTransactions()->sum('points') >= 1000,
            'redeemer' => fn () => $user->courseRedemptions()->exists(),
        ];

        foreach ($checks as $code => $condition) {
            if (in_array($code, $unlocked, true)) {
                continue;
            }

            try {
                if ($condition()) {
                    DB::table('user_badges')->insertOrIgnore([
                        'user_id' => $user->id,
                        'badge_code' => $code,
                        'unlocked_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $newly[] = $code;
                }
            } catch (\Throwable $e) {
                // ignore missing tables/columns during partial migrations
            }
        }

        return $newly;
    }

    /**
     * @return list<array{code:string, name:string, description:string, icon:string, unlocked:bool, unlockedAt:?string}>
     */
    public function forUser(User $user): array
    {
        $unlockedRows = DB::table('user_badges')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('badge_code');

        $result = [];
        foreach (self::catalog() as $code => $meta) {
            $row = $unlockedRows->get($code);
            $result[] = [
                'code' => $code,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'unlocked' => $row !== null,
                'unlockedAt' => $row?->unlocked_at !== null ? (string) $row->unlocked_at : null,
            ];
        }

        return $result;
    }
}
