<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = ['user_id', 'quiz_id', 'score', 'total', 'passed', 'answers', 'question_order', 'option_order'];

    protected function casts(): array
    {
        return ['answers' => 'array', 'passed' => 'boolean', 'question_order' => 'array', 'option_order' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
