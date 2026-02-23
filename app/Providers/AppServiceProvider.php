<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User\PersonalAccessToken;
use App\Models\User\User;
use App\Services\Advertisement\AdvertisementService;
use App\Services\Auth\AuthService;
use App\Services\Contracts\Advertisement\AdvertisementServiceInterface;
use App\Services\Contracts\Auth\AuthServiceInterface;
use App\Services\Contracts\Course\CourseServiceInterface;
use App\Services\Contracts\Education\PhraseServiceInterface;
use App\Services\Contracts\Lesson\LessonServiceInterface;
use App\Services\Contracts\Log\DatabaseLogsProviderInterface;
use App\Services\Contracts\Mail\MailServiceInterface;
use App\Services\Contracts\Payment\SubscriptionServiceInterface;
use App\Services\Contracts\Progress\CourseProgressServiceInterface;
use App\Services\Contracts\Review\CourseReviewServiceInterface;
use App\Services\Contracts\Storage\AudioStorageInterface;
use App\Services\Contracts\Storage\ImageStorageInterface;
use App\Services\Contracts\Topic\TopicServiceInterface;
use App\Services\Contracts\TTSServiceInterface;
use App\Services\Contracts\User\UserProfileServiceInterface;
use App\Services\Contracts\User\UserServiceInterface;
use App\Services\Course\CourseService;
use App\Services\Lesson\LessonService;
use App\Services\Log\DatabaseLogsProvider;
use App\Services\Payment\SubscriptionService;
use App\Services\Phrase\PhraseService;
use App\Services\Progress\CourseProgressService;
use App\Services\Review\CourseReviewService;
use App\Services\Storage\AudioStorageService;
use App\Services\Storage\ImageStorageService;
use App\Services\Topic\TopicService;
use App\Services\TTSService;
use App\Services\User\UserProfileService;
use App\Services\User\UserService;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Sanctum\Sanctum;
use Stripe\Stripe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(UserServiceInterface::class, UserService::class);
        $this->app->singleton(UserProfileServiceInterface::class, UserProfileService::class);
        $this->app->singleton(PhraseServiceInterface::class, PhraseService::class);
        $this->app->singleton(AuthServiceInterface::class, AuthService::class);
        $this->app->singleton(TTSServiceInterface::class, TTSService::class);

        $this->app->singleton(CourseServiceInterface::class, CourseService::class);
        $this->app->singleton(TopicServiceInterface::class, TopicService::class);
        $this->app->singleton(LessonServiceInterface::class, LessonService::class);
        $this->app->singleton(AudioStorageInterface::class, AudioStorageService::class);
        $this->app->singleton(ImageStorageInterface::class, ImageStorageService::class);
        $this->app->singleton(CourseReviewServiceInterface::class, CourseReviewService::class);
        $this->app->singleton(AdvertisementServiceInterface::class, AdvertisementService::class);
        $this->app->singleton(CourseProgressServiceInterface::class, CourseProgressService::class);
        $this->app->singleton(SubscriptionServiceInterface::class, SubscriptionService::class);
        $this->app->singleton(DatabaseLogsProviderInterface::class, DatabaseLogsProvider::class);
        // Bind the concrete mail service implementation to the contract
        $this->app->singleton(MailServiceInterface::class, \App\Services\Mail\MailService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Cashier::useCustomerModel(User::class);

        Stripe::setApiKey(config('cashier.secret'));
    }
}
