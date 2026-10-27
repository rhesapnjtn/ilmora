<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Course;
use App\Models\CourseRedemption;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Carbon;

class PointsService
{
    public const DAILY_CHECKIN_BASE = 5;

    public const LESSON_COMPLETE = 2;

    public const COURSE_COMPLETE = 50;

    public const QUIZ_PASS = 20;

    public function balance(User $user): int
    {
        return (int) $user->pointTransactions()->sum('points');
    }

    public function award(
        User $user,
        string $type,
        int $points,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null,
    ): ?PointTransaction {
        if ($points === 0) {
            return null;
        }

        if ($referenceType !== null && $referenceId !== null
            && PointTransaction::query()
                ->where('user_id', $user->id)
                ->where('type', $type)
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->exists()) {
            return null;
        }

        return PointTransaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'points' => $points,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);
    }

    /**
     * @return array{awarded:int, streak:int, already_checked_in:bool, balance:int}
     */
    public function checkin(User $user): array
    {
        $today = Carbon::today();

        if ($user->last_checkin_at !== null && $user->last_checkin_at->isSameDay($today)) {
            return [
                'awarded' => 0,
                'streak' => (int) $user->current_streak,
                'already_checked_in' => true,
                'balance' => $this->balance($user),
            ];
        }

        $yesterday = $today->copy()->subDay();
        $streak = ($user->last_checkin_at !== null && $user->last_checkin_at->isSameDay($yesterday))
            ? (int) $user->current_streak + 1
            : 1;

        $awarded = self::DAILY_CHECKIN_BASE + min($streak, 30);

        if ($streak === 7) {
            $awarded += 20;
        } elseif ($streak === 30) {
            $awarded += 100;
        } elseif ($streak === 100) {
            $awarded += 500;
        }

        $user->forceFill([
            'current_streak' => $streak,
            'longest_streak' => max((int) $user->longest_streak, $streak),
            'last_checkin_at' => $today->toDateString(),
        ])->save();

        $this->award($user, 'checkin', $awarded, 'user', (int) $user->id, "Daily check-in (streak {$streak})");

        return [
            'awarded' => $awarded,
            'streak' => $streak,
            'already_checked_in' => false,
            'balance' => $this->balance($user),
        ];
    }

    public function redeemCourse(User $user, Course $course): CourseRedemption
    {
        if (! $course->published) {
            throw new \RuntimeException('Kursus tidak tersedia untuk ditukar.');
        }

        if ($course->points_cost === null) {
            throw new \RuntimeException('Kursus ini tidak dapat ditukar dengan poin.');
        }

        if ($user->courseRedemptions()->where('course_id', $course->id)->exists()) {
            throw new \RuntimeException('Kursus ini sudah ditukar sebelumnya.');
        }

        $balance = $this->balance($user);

        if ($balance < (int) $course->points_cost) {
            throw new \RuntimeException('Poin tidak cukup untuk menukar kursus ini.');
        }

        $this->award(
            $user,
            'redeem',
            -(int) $course->points_cost,
            'course',
            (int) $course->id,
            "Tukar poin untuk kursus: {$course->title}",
        );

        return CourseRedemption::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'points_spent' => (int) $course->points_cost,
        ]);
    }
}
