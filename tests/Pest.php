<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Unit tests use Tests\TestCase so the Laravel application is fully
| bootstrapped (config, DB, facades, etc.) for every test.
| Feature tests do the same and additionally refresh the database.
|
*/

uses(Tests\TestCase::class)->in('Unit', 'Feature');
