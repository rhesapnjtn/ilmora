<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\CourseLesson;
use App\Models\LessonDiscussion;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DiscussionPaginationTest extends TestCase
{
    use RefreshDatabase;

    private const COURSE = 'laravel-vue-3-full-stack';

    private const LESSON = '2-1';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->seed(CourseSeeder::class);
    }

    public function test_discussions_are_paginated(): void
    {
        $user = User::factory()->create();
        $lesson = CourseLesson::query()->where('slug', self::LESSON)->firstOrFail();

        foreach (range(1, 12) as $index) {
            LessonDiscussion::create([
                'lesson_id' => $lesson->id,
                'user_id' => $user->id,
                'body' => 'Diskusi '.$index,
            ]);
        }

        $token = $user->createToken('test')->plainTextToken;
        $url = '/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions';

        $this->withToken($token)
            ->getJson($url.'?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'discussions')
            ->assertJsonPath('meta.currentPage', 1)
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.hasMore', true);

        $this->withToken($token)
            ->getJson($url.'?per_page=10&page=2')
            ->assertOk()
            ->assertJsonCount(2, 'discussions')
            ->assertJsonPath('meta.currentPage', 2)
            ->assertJsonPath('meta.hasMore', false);
    }

    public function test_per_page_is_clamped_to_the_maximum(): void
    {
        $user = User::factory()->create();
        $url = '/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions';

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson($url.'?per_page=500')
            ->assertOk()
            ->assertJsonPath('meta.perPage', 50);
    }

    public function test_discussion_writes_are_rate_limited(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $url = '/api/courses/'.self::COURSE.'/lessons/'.self::LESSON.'/discussions';

        foreach (range(1, 10) as $index) {
            $this->withToken($token)
                ->postJson($url, ['body' => 'Diskusi '.$index])
                ->assertCreated();
        }

        $this->withToken($token)
            ->postJson($url, ['body' => 'Diskusi ke-11'])
            ->assertStatus(429);
    }
}
