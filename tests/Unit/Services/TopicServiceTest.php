<?php

declare(strict_types=1);

use App\DTO\Topic\UpdateTopicDTO;
use App\Models\Education\Topic;
use App\Services\Topic\TopicService;

beforeEach(function () {
    $this->service = new TopicService();
});

afterEach(function () {
    Mockery::close();
});

test('updateTopic updates topic data correctly', function () {
    $topic = Mockery::mock(Topic::class)->makePartial();
    $topic->shouldAllowMockingProtectedMethods();
    
    $dto = new UpdateTopicDTO(
        id: 1,
        title: 'Updated Topic Title'
    );

    $topic->shouldReceive('update')->once()->with(['title' => 'Updated Topic Title'])->andReturn(true);
    $topic->shouldReceive('refresh')->once()->andReturn($topic);

    $result = $this->service->updateTopic($topic, $dto);

    expect($result)->toBeInstanceOf(Topic::class);
});

test('deleteTopic returns true on successful deletion', function () {
    $topic = Mockery::mock(Topic::class)->makePartial();
    $topic->shouldAllowMockingProtectedMethods();
    
    $topic->shouldReceive('delete')->once()->andReturn(true);

    $result = $this->service->deleteTopic($topic);

    expect($result)->toBeTrue();
});

test('publish marks topic as active', function () {
    $topic = Mockery::mock(Topic::class)->makePartial();
    $topic->shouldAllowMockingProtectedMethods();
    
    $topic->shouldReceive('update')->once()->with(['is_active' => true])->andReturn(true);
    $topic->shouldReceive('save')->once()->andReturn(true);
    $topic->shouldReceive('refresh')->once()->andReturn($topic);

    $result = $this->service->publish($topic);

    expect($result)->toBeInstanceOf(Topic::class);
});

test('unpublish marks topic as inactive', function () {
    $topic = Mockery::mock(Topic::class)->makePartial();
    $topic->shouldAllowMockingProtectedMethods();
    
    $topic->shouldReceive('update')->once()->with(['is_active' => false])->andReturn(true);
    $topic->shouldReceive('save')->once()->andReturn(true);
    $topic->shouldReceive('refresh')->once()->andReturn($topic);

    $result = $this->service->unpublish($topic);

    expect($result)->toBeInstanceOf(Topic::class);
});
