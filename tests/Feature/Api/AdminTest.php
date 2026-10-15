<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Course;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'is_free' => true,
            ]);

        $response->assertCreated()->assertJsonPath('lesson.title', 'Pelajaran Baru');

        $lessonId = $response->json('lesson.id');

        $this->withToken($admin->createToken('test')->plainTextToken)
            ->deleteJson("/api/admin/courses/{$course->id}/lessons/{$lessonId}")
            ->assertOk();

        $this->assertDatabaseMissing('course_lessons', ['id' => $lessonId]);
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
