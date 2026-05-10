<?php

declare(strict_types=1);

namespace Tests;

use App\Services\MailService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        putenv('CACHE_STORE=array');
        putenv('QUEUE_CONNECTION=sync');
        putenv('SESSION_DRIVER=array');
        putenv('MAIL_MAILER=array');
        $_ENV['CACHE_STORE'] = 'array';
        $_ENV['QUEUE_CONNECTION'] = 'sync';
        $_ENV['SESSION_DRIVER'] = 'array';
        $_ENV['MAIL_MAILER'] = 'array';
        $_SERVER['CACHE_STORE'] = 'array';
        $_SERVER['QUEUE_CONNECTION'] = 'sync';
        $_SERVER['SESSION_DRIVER'] = 'array';
        $_SERVER['MAIL_MAILER'] = 'array';

        parent::setUp();

        $this->app['config']->set('session.driver', 'array');
        $this->app['config']->set('cache.default', 'array');
        $this->app['config']->set('queue.default', 'sync');
        $this->app['config']->set('mail.default', 'array');

        $this->app->forgetInstance('session');
        $this->app->forgetInstance('session.store');
        $this->app->forgetInstance('cache');
        $this->app->forgetInstance('cache.store');
        $this->app->forgetInstance('mailer');
        $this->app->forgetInstance(MailService::class);
    }
}
