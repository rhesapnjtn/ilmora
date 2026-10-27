<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'title',
        'summary',
        'description',
        'category',
        'category_name',
        'level',
        'price',
        'thumbnail',
        'rating',
        'review_count',
        'students',
        'duration_hours',
        'instructor_name',
        'instructor_role',
        'accent',
        'popular',
        'is_free',
        'intro_video_url',
        'published',
        'progress',
        'learn',
        'includes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'students' => 'integer',
            'duration_hours' => 'integer',
            'progress' => 'integer',
            'popular' => 'boolean',
            'is_free' => 'boolean',
            'published' => 'boolean',
            'learn' => 'array',
            'includes' => 'array',
        ];
    }

    /**
     * @return HasMany<CourseLesson, $this>
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('order');
    }

    /**
     * @return HasMany<CourseEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
            ->withPivot(['enrolled_at', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<CourseReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    /**
     * @return HasMany<Quiz, $this>
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
