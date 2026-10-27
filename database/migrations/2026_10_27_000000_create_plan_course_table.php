<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Plans are now tiered: a plan only unlocks the courses explicitly assigned
     * to it. Before this table existed every active subscription unlocked the
     * whole catalog, so the migration backfills the mapping implied by the
     * marketing copy of the seeded plans:
     *
     * - basic   -> courses whose level is "Pemula" (the "kursus dasar")
     * - premium -> every published course ("seluruh katalog kursus")
     * - pro     -> every published course (inherits premium)
     *
     * Any other plan stays empty on purpose: access is denied by default until
     * an administrator assigns courses to it.
     */
    public function up(): void
    {
        Schema::create('plan_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['plan_id', 'course_id']);
        });

        $this->backfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_course');
    }

    private function backfill(): void
    {
        $publishedCourses = DB::table('courses')
            ->where('published', true)
            ->orderBy('id')
            ->get(['id', 'level']);

        if ($publishedCourses->isEmpty()) {
            return;
        }

        $allCourseIds = $publishedCourses->pluck('id')->map(fn (int|string $id): int => (int) $id)->all();
        $starterCourseIds = $publishedCourses
            ->where('level', 'Pemula')
            ->pluck('id')
            ->map(fn (int|string $id): int => (int) $id)
            ->all();

        $mapping = [
            'basic' => $starterCourseIds,
            'premium' => $allCourseIds,
            'pro' => $allCourseIds,
        ];

        foreach ($mapping as $planSlug => $courseIds) {
            $planId = DB::table('plans')->where('slug', $planSlug)->value('id');

            if ($planId === null) {
                continue;
            }

            $timestamp = now();

            $rows = array_map(fn (int $courseId): array => [
                'plan_id' => (int) $planId,
                'course_id' => $courseId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], $courseIds);

            if ($rows !== []) {
                DB::table('plan_course')->insertOrIgnore($rows);
            }
        }
    }
};
