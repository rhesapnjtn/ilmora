<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_free' => 'boolean',
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
}
