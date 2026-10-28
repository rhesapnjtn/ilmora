<?php

use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Api\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseReviewController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DiscussionController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\LearningController;
use App\Http\Controllers\Api\LessonNoteController;
use App\Http\Controllers\Api\NoteListController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\PointsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\RedeemController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\AvatarController;
use App\Models\Category;
use App\Services\BadgeService;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:auth');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');

Route::post('/password/forgot', [PasswordController::class, 'forgot'])->middleware('throttle:auth');
Route::post('/password/reset', [PasswordController::class, 'reset'])->middleware('throttle:auth');

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);

Route::get('/categories', function () {
    return response()->json([
        'categories' => Category::query()->orderBy('name')->get(['slug', 'name']),
    ]);
});

Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify']);
Route::get('/plans', [SubscriptionController::class, 'index']);
Route::get('/plans/{slug}', [SubscriptionController::class, 'show']);
Route::get('/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/badges', function () {
    return response()->json(['badges' => BadgeService::catalog()]);
});

Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::post('/profile/avatar', [AvatarController::class, 'store']);
    Route::delete('/profile/avatar', [AvatarController::class, 'destroy']);

    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [PasswordController::class, 'update'])->middleware('throttle:auth');

    Route::get('/dashboard', DashboardController::class);

    Route::get('/notes', [NoteListController::class, 'index']);

    Route::get('/courses/{slug}/lessons/{lesson}', [LearningController::class, 'show']);
    Route::post('/courses/{slug}/lessons/{lesson}/complete', [LearningController::class, 'toggleComplete']);

    Route::get('/courses/{slug}/lessons/{lesson}/note', [LessonNoteController::class, 'show']);
    Route::put('/courses/{slug}/lessons/{lesson}/note', [LessonNoteController::class, 'update']);

    Route::get('/courses/{slug}/reviews', [CourseReviewController::class, 'index']);
    Route::post('/courses/{slug}/reviews', [CourseReviewController::class, 'store']);
    Route::delete('/courses/{slug}/reviews', [CourseReviewController::class, 'destroy']);

    Route::get('/courses/{slug}/quiz', [QuizController::class, 'show']);
    Route::post('/courses/{slug}/quiz/submit', [QuizController::class, 'submit']);

    Route::post('/courses/{slug}/wishlist', [WishlistController::class, 'toggle']);
    Route::delete('/courses/{slug}/wishlist', [WishlistController::class, 'destroy']);

    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{slug}', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{slug}', [WishlistController::class, 'destroy']);

    Route::get('/courses/{slug}/lessons/{lesson}/discussions', [DiscussionController::class, 'index']);

    Route::middleware('throttle:discussions')->group(function () {
        Route::post('/courses/{slug}/lessons/{lesson}/discussions', [DiscussionController::class, 'store']);
        Route::put('/courses/{slug}/lessons/{lesson}/discussions/{discussion}', [DiscussionController::class, 'update']);
        Route::delete('/courses/{slug}/lessons/{lesson}/discussions/{discussion}', [DiscussionController::class, 'destroy']);
        Route::post('/courses/{slug}/lessons/{lesson}/discussions/{discussion}/accept', [DiscussionController::class, 'accept']);
        Route::delete('/courses/{slug}/lessons/{lesson}/discussions/{discussion}/accept', [DiscussionController::class, 'unaccept']);
    });

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{id}', [CertificateController::class, 'show']);
    Route::get('/certificates/{id}/download', [CertificateController::class, 'download']);

    Route::get('/subscriptions', [SubscriptionController::class, 'mine']);
    Route::post('/subscriptions', [SubscriptionController::class, 'store']);
    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'order']);
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume']);

    Route::get('/points', [PointsController::class, 'show']);
    Route::post('/checkin', [CheckinController::class, 'store']);
    Route::post('/courses/{slug}/redeem', [RedeemController::class, 'store']);

    Route::get('/payments', [PaymentController::class, 'index']);

    Route::get('/chat/topics', [ChatController::class, 'topics']);
    Route::get('/chat/conversations', [ChatController::class, 'index']);
    Route::post('/chat/conversations', [ChatController::class, 'store']);
    Route::get('/chat/conversations/{conversation}', [ChatController::class, 'show']);
    Route::post('/chat/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/chat/conversations/{conversation}/attachments', [ChatController::class, 'uploadAttachment']);
    Route::post('/chat/conversations/{conversation}/typing', [ChatController::class, 'typing']);
    Route::post('/chat/conversations/{conversation}/stop-typing', [ChatController::class, 'stopTyping']);
    Route::get('/chat/conversations/{conversation}/poll', [ChatController::class, 'poll']);
    Route::post('/chat/conversations/{conversation}/close', [ChatController::class, 'close']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class);

    Route::get('/chat/conversations', [AdminChatController::class, 'index']);
    Route::get('/chat/conversations/{conversation}', [AdminChatController::class, 'show']);
    Route::post('/chat/conversations/{conversation}/messages', [AdminChatController::class, 'sendMessage']);
    Route::post('/chat/conversations/{conversation}/attachments', [ChatController::class, 'uploadAttachment']);
    Route::post('/chat/conversations/{conversation}/typing', [ChatController::class, 'typing']);
    Route::post('/chat/conversations/{conversation}/stop-typing', [ChatController::class, 'stopTyping']);
    Route::get('/chat/conversations/{conversation}/poll', [AdminChatController::class, 'poll']);
    Route::post('/chat/conversations/{conversation}/close', [AdminChatController::class, 'close']);
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::get('/categories/{category}', [AdminCategoryController::class, 'show']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

    Route::get('/courses', [AdminCourseController::class, 'index']);
    Route::post('/courses', [AdminCourseController::class, 'store']);
    Route::get('/courses/{course}', [AdminCourseController::class, 'show']);
    Route::put('/courses/{course}', [AdminCourseController::class, 'update']);
    Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy']);
    Route::post('/courses/{course}/publish', [AdminCourseController::class, 'togglePublish']);

    Route::post('/courses/{course}/lessons', [AdminLessonController::class, 'store']);
    Route::put('/courses/{course}/lessons/{lesson}', [AdminLessonController::class, 'update']);
    Route::delete('/courses/{course}/lessons/{lesson}', [AdminLessonController::class, 'destroy']);
    Route::post('/courses/{course}/lessons/{lesson}/video', [AdminLessonController::class, 'uploadVideo']);
    Route::delete('/courses/{course}/lessons/{lesson}/video', [AdminLessonController::class, 'destroyVideo']);

    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

    Route::get('/plans', [AdminPlanController::class, 'index']);
    Route::post('/plans', [AdminPlanController::class, 'store']);
    Route::get('/plans/{plan}', [AdminPlanController::class, 'show']);
    Route::put('/plans/{plan}', [AdminPlanController::class, 'update']);
    Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy']);

    Route::get('/chat/conversations', [AdminChatController::class, 'index']);
    Route::get('/chat/conversations/{conversation}', [AdminChatController::class, 'show']);
    Route::post('/chat/conversations/{conversation}/messages', [AdminChatController::class, 'sendMessage']);
    Route::get('/chat/conversations/{conversation}/poll', [AdminChatController::class, 'poll']);
    Route::post('/chat/conversations/{conversation}/close', [AdminChatController::class, 'close']);

    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index']);
    Route::get('/payments', [AdminPaymentController::class, 'index']);

    Route::get('/reviews', [AdminReviewController::class, 'index']);
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy']);
    Route::post('/payments/{payment}/approve', [AdminPaymentController::class, 'approve']);
    Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject']);
});
