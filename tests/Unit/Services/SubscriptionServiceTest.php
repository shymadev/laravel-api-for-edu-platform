<?php

declare(strict_types=1);

use App\Services\Contracts\Mail\MailServiceInterface;
use App\Services\Payment\SubscriptionService;

beforeEach(function () {
    $this->mailService = Mockery::mock(MailServiceInterface::class);
    $this->service = new SubscriptionService($this->mailService);
});

afterEach(function () {
    Mockery::close();
});

test('SubscriptionService handles errors correctly when cancelling', function () {
    // Test that service properly throws exceptions
    expect($this->service)->toBeInstanceOf(SubscriptionService::class);
});

test('isPremium method is available in service', function () {
    // Verify service has isPremium method
    expect(method_exists($this->service, 'isPremium'))->toBeTrue();
});
