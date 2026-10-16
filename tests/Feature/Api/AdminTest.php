<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CourseSeeder::class);
    }

    public function test_guests_cannot_access_admin_endpoints(): void
    {
        $this->getJson('/api/admin/dashboard')->assertStatus(401);
        $this->getJson('/api/admin/courses')->assertStatus(401);
        $this->getJson('/api/admin/users')->assertStatus(401);
        $this->getJson('/api/admin/plans')->assertStatus(401);
    }

    public function test_regular_users_are_forbidden_from_admin_endpoints(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->getJson('/api/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_admin_can_view_dashboard_statistics(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'stats' => ['users', 'courses', 'publishedCourses', 'activeSubscriptions', 'revenue'],
                'recentUsers',
                'recentPayments',
                'subscriptionBreakdown',
            ]);
    }

    public function test_admin_can_list_courses(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/admin/courses')
            ->assertOk()
            ->assertJsonStructure(['courses', 'meta' => ['page', 'total', 'lastPage']]);
    }

    public function test_admin_can_create_a_course(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson('/api/admin/courses', [
                'title' => 'Kursus Baru Admin',
                'summary' => 'Ringkasan kursus baru.',
                'price' => 150000,
                'category' => 'pemrograman',
                'category_name' => 'Pemrograman',
                'level' => 'Pemula',
                'published' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('course.title', 'Kursus Baru Admin');

        $this->assertDatabaseHas('courses', [
            'title' => 'Kursus Baru Admin',
            'slug' => 'kursus-baru-admin',
        ]);
    }

    public function test_admin_can_update_and_publish_a_course(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::query()->firstOrFail();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->putJson("/api/admin/courses/{$course->id}", [
                'title' => 'Judul Diperbarui',
                'price' => 100000,
            ])
            ->assertOk()
            ->assertJsonPath('course.title', 'Judul Diperbarui');

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson("/api/admin/courses/{$course->id}/publish")
            ->assertOk();

        $this->assertDatabaseHas('courses', ['id' => $course->id, 'published' => false]);
    }

    public function test_admin_can_delete_a_course(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::query()->firstOrFail();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->deleteJson("/api/admin/courses/{$course->id}")
            ->assertOk();

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    public function test_admin_can_manage_lessons(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::query()->firstOrFail();

        $response = $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson("/api/admin/courses/{$course->id}/lessons", [
                'title' => 'Pelajaran Baru',
                'module' => 'Modul Baru',
                'duration' => '10:00',
                'video_url' => 'https://www.youtube.com/watch?v=abc123xyz',
                'is_free' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('lesson.title', 'Pelajaran Baru')
            ->assertJsonPath('lesson.videoUrl', 'https://www.youtube.com/watch?v=abc123xyz');

        $lessonId = $response->json('lesson.id');

        $this->assertDatabaseHas('course_lessons', [
            'id' => $lessonId,
            'video_url' => 'https://www.youtube.com/watch?v=abc123xyz',
        ]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->putJson("/api/admin/courses/{$course->id}/lessons/{$lessonId}", [
                'title' => 'Pelajaran Baru',
                'video_url' => 'https://example.com/intro.mp4',
            ])
            ->assertOk()
            ->assertJsonPath('lesson.videoUrl', 'https://example.com/intro.mp4');

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->deleteJson("/api/admin/courses/{$course->id}/lessons/{$lessonId}")
            ->assertOk();

        $this->assertDatabaseMissing('course_lessons', ['id' => $lessonId]);
    }

    public function test_admin_can_upload_and_delete_lesson_video(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::query()->firstOrFail();

        $lesson = $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson("/api/admin/courses/{$course->id}/lessons", [
                'title' => 'Pelajaran Video',
            ])->assertCreated()->json('lesson');

        $path = tempnam(sys_get_temp_dir(), 'vid');
        file_put_contents($path, "\x00\x00\x00\x18ftypisom\x00\x00\x02\x00isomiso2avc1mp41");
        $video = new UploadedFile($path, 'video.mp4', 'video/mp4', null, true);

        $response = $this->withToken($admin->createToken('test')->plainTextToken)
            ->post("/api/admin/courses/{$course->id}/lessons/{$lesson['id']}/video", ['video' => $video])
            ->assertOk();

        $videoUrl = $response->json('lesson.videoUrl');

        $this->assertIsString($videoUrl);
        $this->assertStringContainsString('/storage/videos/', $videoUrl);

        $this->assertDatabaseHas('course_lessons', [
            'id' => $lesson['id'],
            'video_url' => $videoUrl,
        ]);

        $pathOnDisk = str_replace(Storage::disk('public')->url(''), '', $videoUrl);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->delete("/api/admin/courses/{$course->id}/lessons/{$lesson['id']}/video")
            ->assertOk()
            ->assertJsonPath('lesson.videoUrl', null);

        Storage::disk('public')->assertMissing($pathOnDisk);
    }

    public function test_admin_cannot_upload_non_video_file_as_lesson_video(): void
    {
        $admin = User::factory()->admin()->create();
        $course = Course::query()->firstOrFail();

        $lesson = $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson("/api/admin/courses/{$course->id}/lessons", [
                'title' => 'Pelajaran Tanpa Video',
            ])->assertCreated()->json('lesson');

        $path = tempnam(sys_get_temp_dir(), 'doc');
        file_put_contents($path, 'This is plain text, not a video.');
        $file = new UploadedFile($path, 'notes.txt', 'text/plain', null, true);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->post("/api/admin/courses/{$course->id}/lessons/{$lesson['id']}/video", ['video' => $file])
            ->assertStatus(422)
            ->assertJsonValidationErrors('video');
    }

    public function test_admin_can_list_users_and_change_a_role(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonStructure(['users', 'meta' => ['total']]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->putJson("/api/admin/users/{$target->id}", ['role' => 'admin'])
            ->assertOk()
            ->assertJsonPath('user.role', 'admin');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'admin']);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->deleteJson("/api/admin/users/{$admin->id}")
            ->assertStatus(422);
    }

    public function test_admin_can_list_plans(): void
    {
        $admin = User::factory()->admin()->create();
        Plan::factory()->count(2)->create();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/admin/plans')
            ->assertOk()
            ->assertJsonStructure([
                'plans' => [['id', 'name', 'slug', 'monthly', 'annual', 'highlighted', 'features', 'isActive', 'sortOrder']],
            ])
            ->assertJsonCount(2, 'plans');
    }

    public function test_admin_can_create_a_plan(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->withToken($admin->createToken('test')->plainTextToken)
            ->postJson('/api/admin/plans', [
                'name' => 'Paket Enterprise',
                'monthly' => 399000,
                'annual' => 3990000,
                'highlighted' => true,
                'is_active' => true,
                'sort_order' => 1,
                'features' => ['Semua akses', 'Support 24/7'],
            ]);

        $response->assertCreated()
            ->assertJsonPath('plan.name', 'Paket Enterprise')
            ->assertJsonPath('plan.slug', 'paket-enterprise');

        $this->assertDatabaseHas('plans', [
            'slug' => 'paket-enterprise',
            'monthly' => 399000,
        ]);
    }

    public function test_admin_can_update_a_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create(['name' => 'Paket Lama', 'monthly' => 100000, 'annual' => 1000000]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->putJson("/api/admin/plans/{$plan->id}", [
                'name' => 'Paket Baru',
                'monthly' => 150000,
                'annual' => 1500000,
            ])
            ->assertOk()
            ->assertJsonPath('plan.name', 'Paket Baru');

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Paket Baru',
            'monthly' => 150000,
        ]);
    }

    public function test_admin_can_delete_an_unused_plan(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = Plan::factory()->create();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->deleteJson("/api/admin/plans/{$plan->id}")
            ->assertOk();

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    public function test_admin_cannot_delete_a_plan_in_use(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $plan = Plan::factory()->create(['name' => 'Premium', 'slug' => 'premium']);

        Subscription::create([
            'user_id' => $owner->id,
            'plan' => 'Premium',
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'amount' => 149000,
            'currency' => 'IDR',
        ]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->deleteJson("/api/admin/plans/{$plan->id}")
            ->assertStatus(422);

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_admin_can_list_subscriptions_and_payments(): void
    {
        $admin = User::factory()->admin()->create();

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/admin/subscriptions')
            ->assertOk()
            ->assertJsonStructure(['subscriptions', 'summary' => ['total', 'active']]);

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->getJson('/api/admin/payments')
            ->assertOk()
            ->assertJsonStructure(['payments', 'summary' => ['total', 'success', 'revenue']]);
    }
}
