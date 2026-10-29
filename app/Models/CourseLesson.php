<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'slug',
        'title',
        'module',
        'duration',
        'description',
        'code',
        'materials',
        'video_url',
        'order',
        'is_free',
        'is_preview',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_free' => 'boolean',
            'is_preview' => 'boolean',
            'materials' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return HasMany<LessonDiscussion, $this>
     */
    public function discussions(): HasMany
    {
        return $this->hasMany(LessonDiscussion::class, 'lesson_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * @return HasMany<CourseLessonCompletion, $this>
     */
    public function completions(): HasMany
    {
        return $this->hasMany(CourseLessonCompletion::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_lesson_completions')
            ->withPivot('completed_at')
            ->withTimestamps();
    }
}
