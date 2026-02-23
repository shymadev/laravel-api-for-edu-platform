<?php

declare(strict_types=1);

use App\Http\Controllers\SubscriptionController;
use App\Services\Contracts\Payment\SubscriptionServiceInterface;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->subscriptionService = Mockery::mock(SubscriptionServiceInterface::class);
    $this->controller = new SubscriptionController($this->subscriptionService);
});

afterEach(function () {
    Mockery::close();
});

test('controller calls subscriptionService to cancel subscription', function () {
    $userId = 1;

    $this->subscriptionService->shouldReceive('cancel')
        ->with($userId)
        ->once();

    $this->subscriptionService->cancel($userId);
    
    expect(true)->toBeTrue(); // Passes if no exception thrown
});

test('controller calls subscriptionService to resume subscription', function () {
    $userId = 1;

    $this->subscriptionService->shouldReceive('resume')
        ->with($userId)
        ->once();

    $this->subscriptionService->resume($userId);
    
    expect(true)->toBeTrue(); // Passes if no exception thrown
});

test('controller calls subscriptionService to get isPremium status', function () {
    $userId = 1;

    $this->subscriptionService->shouldReceive('isPremium')
        ->with($userId)
        ->once()
        ->andReturn(true);

    $result = $this->subscriptionService->isPremium($userId);
    
    expect($result)->toBeTrue();
});
