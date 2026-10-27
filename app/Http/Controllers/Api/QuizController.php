<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private readonly PointsService $points) {}

    public function show(Request $request, string $slug): JsonResponse
    {
        $course = Course::query()->where('slug', $slug)->firstOrFail();
        $quiz = $course->quizzes()->with('questions')->first();

        if ($quiz === null) {
            return response()->json(['message' => 'Kuis tidak tersedia.'], 404);
        }

        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'passingScore' => $quiz->passing_score,
                'questions' => $quiz->questions->map(fn ($q): array => [
                    'id' => $q->id,
                    'question' => $q->question,
                    'options' => $q->options,
                    'points' => $q->points,
                ])->all(),
            ],
        ]);
    }

    public function submit(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::query()->where('slug', $slug)->firstOrFail();
        $quiz = $course->quizzes()->with('questions')->firstOrFail();

        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $answers = $validated['answers'];
        $score = 0;
        $total = 0;

        foreach ($quiz->questions as $question) {
            $total += $question->points;
            $selected = $answers[$question->id] ?? null;
            if ($selected !== null && (int) $selected === (int) $question->correct_option) {
                $score += $question->points;
            }
        }

        $passed = $total > 0 && ($score / $total * 100) >= $quiz->passing_score;

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'score' => $score,
            'total' => $total,
            'passed' => $passed,
            'answers' => $answers,
        ]);

        if ($passed) {
            $this->points->award(
                $user,
                'quiz_pass',
                PointsService::QUIZ_PASS,
                'quiz',
                (int) $quiz->id,
                'Lulus quiz: '.$course->title,
            );
        }

        return response()->json([
            'attempt' => $attempt,
            'score' => $score,
            'total' => $total,
            'passed' => $passed,
            'percent' => $total > 0 ? (int) round(($score / $total) * 100) : 0,
        ]);
    }
}
