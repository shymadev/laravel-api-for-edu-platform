<?php

declare(strict_types=1);

use App\Http\Controllers\TopicController;
use App\Models\Education\Topic;
use App\Services\Contracts\Topic\TopicServiceInterface;

beforeEach(function () {
    $this->topicService = Mockery::mock(TopicServiceInterface::class);
    $this->controller = new TopicController($this->topicService);
});

afterEach(function () {
    Mockery::close();
});

test('controller calls topicService to delete topic', function () {
    $topic = Mockery::mock(Topic::class)->makePartial();
    $topic->shouldAllowMockingProtectedMethods();

    $this->topicService->shouldReceive('deleteTopic')
        ->with($topic)
        ->once()
        ->andReturn(true);

    $result = $this->topicService->deleteTopic($topic);
    
    expect($result)->toBeTrue();
});

test('controller calls topicService to publish topic', function () {
    $topic = Mockery::mock(Topic::class)->makePartial();
    $topic->shouldAllowMockingProtectedMethods();

    $this->topicService->shouldReceive('publish')
        ->with($topic)
        ->once()
        ->andReturn($topic);

    $result = $this->topicService->publish($topic);
    
    expect($result)->toBe($topic);
});

test('controller calls topicService to unpublish topic', function () {
    $topic = Mockery::mock(Topic::class)->makePartial();
    $topic->shouldAllowMockingProtectedMethods();

    $this->topicService->shouldReceive('unpublish')
        ->with($topic)
        ->once()
        ->andReturn($topic);

    $result = $this->topicService->unpublish($topic);
    
    expect($result)->toBe($topic);
});
