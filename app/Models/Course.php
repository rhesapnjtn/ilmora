<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
