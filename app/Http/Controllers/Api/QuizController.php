<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Services\BadgeService;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QuizController extends Controller
{
    public function __construct(
        private readonly PointsService $points,
        private readonly BadgeService $badges,
    ) {}

    public function show(Request $request, string $slug): JsonResponse
    {
        $course = Course::query()->where('slug', $slug)->firstOrFail();
        $quiz = $course->quizzes()->with('questions')->first();

        if ($quiz === null) {
            return response()->json(['message' => 'Kuis tidak tersedia.'], 404);
        }

        $user = $request->user();
        $cacheKey = $this->orderCacheKey($user?->id, $quiz->id);

        $questions = $quiz->questions->values();

        if ($quiz->shuffle_questions) {
            $questionOrder = $questions->pluck('id')->shuffle()->values()->all();
        } else {
            $questionOrder = $questions->pluck('id')->values()->all();
        }

        $orderedQuestions = collect($questionOrder)
            ->map(fn (int $id) => $questions->firstWhere('id', $id))
            ->filter()
            ->values();

        $optionOrder = [];
        $payload = $orderedQuestions->map(function (QuizQuestion $q) use ($quiz, &$optionOrder): array {
            $options = array_values($q->options ?? []);

            if ($quiz->shuffle_options && count($options) > 1) {
                $indices = array_keys($options);
                shuffle($indices);
                $optionOrder[$q->id] = $indices;
                $shuffled = [];
                foreach ($indices as $i) {
                    $shuffled[] = $options[$i];
                }
            } else {
                $optionOrder[$q->id] = array_keys($options);
                $shuffled = $options;
            }

            return [
                'id' => $q->id,
                'question' => $q->question,
                'options' => $shuffled,
                'points' => $q->points,
            ];
        })->all();

        Cache::put($cacheKey, [
            'question_order' => $questionOrder,
            'option_order' => $optionOrder,
        ], now()->addMinutes(30));

        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'passingScore' => $quiz->passing_score,
                'timeLimitMinutes' => $quiz->time_limit_minutes,
                'maxAttempts' => $quiz->max_attempts,
                'questions' => $payload,
            ],
        ]);
    }

    public function submit(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $course = Course::query()->where('slug', $slug)->firstOrFail();
        $quiz = $course->quizzes()->with('questions')->firstOrFail();

        if ($quiz->max_attempts !== null) {
            $attempts = QuizAttempt::query()
                ->where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->count();

            if ($attempts >= $quiz->max_attempts) {
                return response()->json(['message' => 'Batas percobaan quiz telah tercapai.'], 422);
            }
        }

        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $answers = $validated['answers'];

        $cached = Cache::get($this->orderCacheKey($user->id, $quiz->id));
        $questionOrder = is_array($cached['question_order'] ?? null) ? $cached['question_order'] : $quiz->questions->pluck('id')->all();
        $optionOrder = is_array($cached['option_order'] ?? null) ? $cached['option_order'] : [];

        $score = 0;
        $total = 0;

        foreach ($quiz->questions as $question) {
            $total += $question->points;
            $selected = $answers[$question->id] ?? null;

            if ($selected === null) {
                continue;
            }

            $displayedIndex = (int) $selected;
            $indices = $optionOrder[$question->id] ?? array_keys($question->options ?? []);
            $originalIndex = $indices[$displayedIndex] ?? $displayedIndex;

            if ($originalIndex === (int) $question->correct_option) {
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
            'question_order' => $questionOrder,
            'option_order' => $optionOrder,
        ]);

        Cache::forget($this->orderCacheKey($user->id, $quiz->id));

        if ($passed) {
            $this->points->award(
                $user,
                'quiz_pass',
                PointsService::QUIZ_PASS,
                'quiz',
                (int) $quiz->id,
                'Lulus quiz: '.$course->title,
            );
            $this->badges->evaluate($user->fresh());
        }

        return response()->json([
            'attempt' => $attempt,
            'score' => $score,
            'total' => $total,
            'passed' => $passed,
            'percent' => $total > 0 ? (int) round(($score / $total) * 100) : 0,
        ]);
    }

    private function orderCacheKey(?int $userId, int $quizId): string
    {
        return 'quiz_order:'.($userId ?? 'guest').':'.$quizId;
    }
}
