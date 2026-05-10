<?php

declare(strict_types=1);

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseReviewController;
use App\Http\Controllers\DifficultyLevelController;
use App\Http\Controllers\FavoritePhraseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\MediaUploadController;
use App\Http\Controllers\PhraseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProgressController;
use App\Models\User\Role;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::get('login', [AuthController::class, 'login']);
});

Route::get('phrases', [PhraseController::class, 'index']);
Route::get('phrases-categories', [PhraseController::class, 'categories']);
Route::post('phrases/resolve', [PhraseController::class, 'resolve']);
Route::get('courses', [CourseController::class, 'index']);
Route::get('courses/{course}', [CourseController::class, 'show']);
Route::get('courses/{course}/topics', [TopicController::class, 'getTopicsByCourseId']);
Route::get('topics/{topic}/lessons', [LessonController::class, 'getLessonsByTopic']);
Route::get('difficulty-levels', [DifficultyLevelController::class, 'index']);
Route::get('advertisements/active', [AdvertisementController::class, 'active']);
Route::get('advertisements', [AdvertisementController::class, 'index']);
Route::get('courses/{courseId}/reviews', [CourseReviewController::class, 'index']);
Route::get('courses/{courseId}/rating', [CourseReviewController::class, 'rating']);
Route::get('lessons/{lessonId}', [LessonController::class, 'show']);
Route::get('topics/{topic}', [TopicController::class, 'show']);
Route::get('statistics/overall', [StatisticsController::class, 'getStatistics']);

Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

Route::post('auth/forgot-password', [ResetPasswordController::class, 'forgotPassword'])
    ->middleware('throttle:10,1');
Route::post('auth/reset-password', [ResetPasswordController::class, 'resetPassword'])
    ->middleware('throttle:10,1');
Route::post(
    'auth/change-password',
    [ChangePasswordController::class, 'changePassword'],
)
    ->middleware('throttle:10,1')
    ->middleware('auth:sanctum')
    ->middleware('is_blocked');

Route::middleware(['auth:sanctum', 'is_blocked'])->group(function (): void {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'currentUser']);

    Route::get('profiles/{id}', [ProfileController::class, 'getProfileById']);
    Route::put('profiles/{id}', [ProfileController::class, 'updateProfileById']);
    Route::post('profiles/{id}/avatar', [ProfileController::class, 'updateProfileAvatar']);

    Route::get('favorite-phrases', [FavoritePhraseController::class, 'index']);
    Route::post('favorite-phrases/toggle', [FavoritePhraseController::class, 'toggle']);
    Route::post('favorite-phrases/toggle-learned', [FavoritePhraseController::class, 'toggleLearned']);

    Route::get('user/completed-lessons', [UserProgressController::class, 'completedLessons']);
    Route::post('user/completed-lessons', [UserProgressController::class, 'markLessonComplete']);
    Route::get('user/courses/progress', [UserProgressController::class, 'coursesProgress']);
    Route::get('user/courses/{courseId}/progress', [UserProgressController::class, 'courseProgress']);
    Route::post('user/courses/{courseId}/stop', [UserProgressController::class, 'stopCourse']);
    Route::post('user/courses/{courseId}/resume', [UserProgressController::class, 'resumeCourse']);
    Route::get('user/statistics', [UserProgressController::class, 'statistics']);
    Route::get('user/lessons/{lessonId}/block-progress', [UserProgressController::class, 'lessonBlockProgress']);
    Route::post('user/lesson-block-progress', [UserProgressController::class, 'saveBlockProgress']);

    Route::post('courses/{courseId}/reviews', [CourseReviewController::class, 'store']);
    Route::delete('courses/{courseId}/reviews', [CourseReviewController::class, 'destroy']);

    Route::prefix('subscription')->group(function (): void {
        Route::post('setup-intent', [SubscriptionController::class, 'createSetupIntent']);
        Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('cancel', [SubscriptionController::class, 'cancel']);
        Route::post('resume', [SubscriptionController::class, 'resume']);
        Route::get('status', [SubscriptionController::class, 'status']);
    });

    Route::get('lessons', [LessonController::class, 'index']);
});

Route::middleware([
    'auth:sanctum',
    'role:' . Role::ADMIN_ROLE_ID . ',' . Role::MODERATOR_ROLE_ID,
    'is_blocked',
])->group(function (): void {
    Route::post('media/video', [MediaUploadController::class, 'uploadVideo']);

    Route::post('phrases', [PhraseController::class, 'store']);
    Route::get('phrases/{phrase}', [PhraseController::class, 'show']);
    Route::put('phrases/{phrase}', [PhraseController::class, 'update']);
    Route::delete('phrases/{phrase}', [PhraseController::class, 'destroy']);

    Route::put('courses/{course}/publish', [CourseController::class, 'publish']);
    Route::put('courses/{course}/unpublish', [CourseController::class, 'unpublish']);
    Route::put('courses/{course}/archive', [CourseController::class, 'archive']);
    Route::put('courses/{course}/unarchive', [CourseController::class, 'unarchive']);

    Route::put('topics/{topic}/publish', [TopicController::class, 'publish']);
    Route::put('topics/{topic}/unpublish', [TopicController::class, 'unpublish']);

    Route::put('lessons/{lesson}/publish', [LessonController::class, 'publish']);
    Route::put('lessons/{lesson}/unpublish', [LessonController::class, 'unpublish']);

    Route::put('advertisements/{advertisement}/publish', [AdvertisementController::class, 'publish']);
    Route::put('advertisements/{advertisement}/unpublish', [AdvertisementController::class, 'unpublish']);

    Route::delete('courses/reviews/{review}', [CourseReviewController::class, 'destroyAdmin']);

    Route::apiResource('courses', CourseController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('topics', TopicController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('lessons', LessonController::class)
        ->only(['store', 'update', 'destroy']);
});

Route::middleware([
    'auth:sanctum',
    'role:' . Role::ADMIN_ROLE_ID,
    'is_blocked',
])->group(function (): void {

    Route::apiResource('users', UserController::class);
    Route::put('users/{user}/block', [UserController::class, 'block']);
    Route::put('users/{user}/unblock', [UserController::class, 'unblock']);

    Route::apiResource('advertisements', AdvertisementController::class)
        ->only(['store', 'update', 'destroy']);

    Route::get('logs', [LogsController::class, 'index']);
});
