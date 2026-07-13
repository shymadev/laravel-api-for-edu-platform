# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run full test suite (lint + analyse + config:clear + tests)
composer test

# Run only unit tests
composer unit-test

# Lint (dry-run, shows diff)
composer lint

# Auto-fix code style
composer fix

# Static analysis (PHPStan level 5)
composer analyse

# Run a single test file or test by name
php artisan test tests/Unit/SomeTest.php
php artisan test --filter=testMethodName
```

## Required conventions

- **PHP**: `^8.2` (see `composer.json`). Prefer typed properties, `readonly` classes where appropriate, and constructor promotion.
- **Strict types**: every PHP file must begin with `declare(strict_types=1);` (enforced by PHP-CS-Fixer `declare_strict_types`).
- **Code style**: PSR-12 + PSR-12 risky rules via `.php-cs-fixer.dist.php` — short array syntax, alphabetically ordered imports, single quotes, trailing commas in multiline arrays/arguments, no unused imports.
- **Static analysis**: new code should pass `composer analyse` (Larastan / PHPStan).
- **Scope**: follow existing naming and folder layout; avoid unrelated refactors in the same change.

## Architecture overview

**Tallksy** — language-learning API: **Laravel 12**, **Sanctum** token auth, **Stripe Cashier** subscriptions, **Redis** queues (see `docker-compose.yml`).

### Domain model

```
Course → Topics → Lessons
```

- **Lessons** store structured **`content` as JSON** on the `lessons` table. The `Lesson` model casts `content` to `array`; `ParagraphParser` turns that payload into typed **paragraph value objects** under `App\Models\Education\Paragraphs\` (e.g. text, video, phrase, test blocks).
- **Phrases** are a separate vocabulary catalog (audio, difficulty, optional transcription). Users attach **favorites** and **learned** state per phrase.
- **Users** have a **role** (user / moderator / admin) via `App\Models\User\Role` (IDs used in routes, e.g. `Role::ADMIN_ROLE_ID`). **Stripe** subscription (Cashier) gates premium features; subscription name **`premium`** matches `SubscriptionService` and `CheckPremiumSubscription`.

### Request → DTO → service → resource

1. **FormRequest** (`app/Http/Requests/`) — validation and authorization; organize by area: `Course/`, `Topic/`, `Lesson/`, `Education/`, `User/`, `Advertisement/`, etc.
2. **DTO** (`app/DTO/`) — `readonly` classes passed into services. Controllers may build DTOs via:
   - `$request->toDTO()` where the FormRequest implements it, or
   - static factories such as `SomeDTO::fromArray($request->validated())` when files / extra normalization are involved.
   Some DTOs use `App\DTO\Traits\ConvertToArrayTrait` for `toArray()` with snake_case keys.
3. **Service** (`app/Services/`) — business logic. Prefer depending on **`app/Services/Contracts/...` interfaces** when the implementation is registered in `AppServiceProvider`. Some services are **concrete singletons** using `#[Singleton]` (see below) and are injected by class name.
4. **Model** — Eloquent under `app/Models/` (`Education/`, `User/`, `Additional/`).
5. **API resource** (`app/Http/Resources/`) — shape JSON responses; use `JsonResource` / collections for lists.

### Service registration patterns

- **Interface → implementation**: most domain services are bound in `AppServiceProvider::register()` (e.g. `UserServiceInterface`, `LessonServiceInterface`, `SubscriptionServiceInterface`).
- **Concrete `#[Singleton]`**: some classes use `Illuminate\Container\Attributes\Singleton` and are resolved by type-hint (e.g. `CourseService`, `AuthService`, `MailService`, password processors). **Do not** duplicate-bind these in the provider unless switching to an interface.
- **Processors / strategies**: multi-step flows (e.g. `ChangePasswordProcessor` with `StandardChangePasswordStrategy` / `GoogleChangePasswordStrategy`) may use manual `bind()` closures when strategies need ordered lists.

### Other structural patterns

- **Actions** (`app/Actions/Auth/`) — focused classes for register, login, Google OAuth callback (`HandleGoogleCallback`).
- **Controllers** — thin; often use `PaginatorTrait` / `SearcherTrait` for list endpoints. Prefer explicit HTTP status codes and JSON shapes consistent with existing controllers.
- **Observers** (`app/Observers/`) — model lifecycle side-effects where registered.
- **Storage** — `AudioStorageInterface`, `ImageStorageInterface`, `FileStorageInterface` with implementations under `app/Services/Storage/`.
- **Events / listeners** — e.g. `PasswordReset` → `SendPasswordResetSuccessEmail` in `AppServiceProvider::boot()`; follow the same pattern for new mail-triggering events.
- **Mail** — Mailable classes under `app/Mail/`; templates under `resources/views/` as used by each Mailable.

### HTTP API surface

- **REST-ish JSON** under `routes/api.php` (default ` /api` prefix).
- **Google OAuth** browser redirects live in `routes/web.php` (`/auth/google`, `/auth/google/callback`).
- **Stripe webhooks** — `POST /api/stripe/webhook` → `StripeWebhookController` (Cashier verification + DB sync) after `StripeWebhookApplicationService` (persists every event to `webhook_events`, uses `App\DTO\Webhook\StripeWebhookEventDTO` for logging / billable lookup). CSRF excepted in `bootstrap/app.php`. `StripeWebhookController` also forwards `customer.subscription.{paused,resumed,trial_will_end}` to Cashier’s `handleCustomerSubscriptionUpdated` so local “all events” streams keep `subscriptions` in sync. Local Docker: `laravel-queue` + `STRIPE_WEBHOOK_FORWARD_TO=http://nginx:8080/api/stripe/webhook` (not `php-fpm:9000` — FastCGI only).

### Middleware aliases (`bootstrap/app.php`)

| Alias        | Purpose |
|--------------|---------|
| `auth:sanctum` | Bearer / session auth (Sanctum) |
| `is_blocked`   | Blocked users cannot proceed |
| `role:...`     | `CheckRole` — comma-separated role IDs (use `Role::ADMIN_ROLE_ID` etc.) |
| `premium`      | `CheckPremiumSubscription` — requires Cashier subscription named `premium` |

Apply `premium` only on routes that must require an active premium subscription (middleware is registered but must be attached per route/group as needed).

### Route authorization (summary)

| Who | Typical middleware |
|-----|---------------------|
| Public | Browse courses, phrases, reviews, ads, stats as defined in `routes/api.php` |
| Authenticated | `auth:sanctum`, `is_blocked` — profile, favorites, progress, subscriptions, reviews write |
| Moderator + admin | `role:` with moderator and admin IDs — publish/unpublish, CRUD courses/topics/lessons (see `routes/api.php`) |
| Admin only | `role:` admin ID — users CRUD, block/unblock, advertisements CRUD, logs |

### API documentation

OpenAPI spec: `resources/swagger/openapi.json` (Swagger UI package in `config/swagger-ui.php`). Update the spec when you add or change public HTTP contracts.

### Testing

**Pest** + PHPUnit; SQLite in-memory for tests (`phpunit.xml`). Use `RefreshDatabase` on feature tests that touch the DB.

---
name: phpunit-testing-pro
description: Senior-level PHPUnit testing skill for Laravel/PHP applications. Use PROACTIVELY when writing tests, creating test suites, mocking dependencies, testing APIs, database testing, or improving test coverage. Covers PHPUnit 10+, Laravel testing helpers, data providers, mocks/stubs, Pest PHP comparison, and testing best practices. Trigger for test creation, test debugging, coverage improvement, or any testing-related questions.
---

# PHPUnit Testing Pro

A comprehensive testing skill for PHP/Laravel applications following industry best practices. Covers PHPUnit 10+, Laravel testing helpers, and modern testing patterns.

## Core Philosophy

Tests should be:
- **Fast** - Isolated, no external dependencies in unit tests
- **Isolated** - Each test is independent, can run in any order
- **Repeatable** - Same result every time, no flaky tests
- **Self-validating** - Clear pass/fail assertions
- **Timely** - Written alongside or before code (TDD)

---

## Quick Start

### Running Tests

```bash
# Run all tests
php artisan test
# or
vendor/bin/phpunit

# Run specific file
php artisan test --filter=UserTest

# Run specific method
php artisan test --filter=test_user_can_login

# Run by group
php artisan test --group=api

# Run in parallel (faster)
php artisan test --parallel

# With coverage
php artisan test --coverage
php artisan test --coverage --min=80
```

### Test Structure (AAA Pattern)

```php
public function test_user_can_create_post(): void
{
    // Arrange - Set up test data
    $user = User::factory()->create();
    $category = Category::factory()->create();

    // Act - Perform the action
    $response = $this->actingAs($user)
        ->postJson('/api/posts', [
            'title' => 'Test Post',
            'content' => 'Content here',
            'category_id' => $category->id,
        ]);

    // Assert - Verify the outcome
    $response->assertCreated()
        ->assertJsonPath('data.title', 'Test Post');

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
        'user_id' => $user->id,
    ]);
}
```

---

## Test Organization

```
tests/
├── Unit/                    # Fast, isolated tests
│   ├── Models/
│   │   └── UserTest.php
│   ├── Services/
│   │   └── PaymentServiceTest.php
│   └── Helpers/
│       └── StringHelperTest.php
├── Feature/                 # HTTP/API tests
│   ├── Api/
│   │   └── v1/
│   │       ├── PostControllerTest.php
│   │       └── UserControllerTest.php
│   ├── Auth/
│   │   └── AuthenticationTest.php
│   └── Web/
│       └── DashboardTest.php
├── Integration/             # Database/API integration
│   └── OrderProcessingTest.php
└── TestCase.php             # Base test class
```

---

## Essential Test Types

### 1. Unit Tests (No Laravel)

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\TaxCalculator;

class TaxCalculatorTest extends TestCase
{
    public function test_calculates_tax_correctly(): void
    {
        $calculator = new TaxCalculator();

        $result = $calculator->calculate(100, 0.20);

        $this->assertEquals(20.0, $result);
    }

    public function test_throws_exception_for_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $calculator = new TaxCalculator();
        $calculator->calculate(-100, 0.20);
    }
}
```

### 2. Feature Tests (Full Laravel)

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Test',
            'content' => 'Content',
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/posts', [
                'title' => 'Test Post',
                'content' => 'Test content here',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'content', 'created_at'],
            ]);
    }
}
```

---

## Data Providers

Use data providers for testing multiple scenarios:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmailValidatorTest extends TestCase
{
    /**
     * @dataProvider validEmailProvider
     */
    public function test_validates_correct_emails(string $email): void
    {
        $this->assertTrue(EmailValidator::isValid($email));
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function test_rejects_invalid_emails(string $email): void
    {
        $this->assertFalse(EmailValidator::isValid($email));
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple email' => ['user@example.com'],
            'with subdomain' => ['user@sub.example.com'],
            'with plus' => ['user+tag@example.com'],
            'with numbers' => ['user123@example.com'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'no @ symbol' => ['userexample.com'],
            'no domain' => ['user@'],
            'no local part' => ['@example.com'],
            'multiple @' => ['user@@example.com'],
            'empty string' => [''],
        ];
    }
}
```

---

## Mocking Strategies

### Mocking Services

```php
public function test_uses_payment_service(): void
{
    $paymentService = $this->mock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('charge')
            ->once()
            ->with(100.00, 'usd')
            ->andReturn(['status' => 'success', 'id' => 'txn_123']);
    });

    // Or using partial mock
    $paymentService = $this->partialMock(PaymentService::class, function ($mock) {
        $mock->shouldReceive('charge')->once();
    });

    $this->app->instance(PaymentService::class, $paymentService);

    // Test your code
}
```

### Mocking Facades

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

public function test_caches_result(): void
{
    Cache::shouldReceive('remember')
        ->once()
        ->with('posts.all', 3600, \Closure::class)
        ->andReturn(collect());

    $response = $this->get('/api/posts');
}

public function test_dispatches_job(): void
{
    Queue::fake();

    // Act
    $response = $this->post('/api/orders', $orderData);

    Queue::assertPushed(ProcessOrder::class, function ($job) use ($orderData) {
        return $job->orderId === $orderData['id'];
    });
}

public function test_sends_email(): void
{
    Mail::fake();

    $user = User::factory()->create();
    $user->notify(new OrderShipped($order));

    Mail::assertSent(OrderShippedEmail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
}
```

---

## Database Testing

### RefreshDatabase vs DatabaseTransactions

```php
// Use RefreshDatabase for feature tests (migrates once)
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeatureTest extends TestCase
{
    use RefreshDatabase;
}

// Use DatabaseMigrations for each test to have fresh migrations
use Illuminate\Foundation\Testing\DatabaseMigrations;

// Use DatabaseTransactions for unit tests (faster, wraps in transaction)
use Illuminate\Foundation\Testing\DatabaseTransactions;
```

### Database Assertions

```php
// Check record exists
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
    'active' => true,
]);

// Check record doesn't exist
$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com',
]);

// Check count
$this->assertDatabaseCount('posts', 5);

// Check model exists
$this->assertModelExists($post);

// Check model is missing (soft deleted)
$this->assertModelMissing($post);

// Check soft deletes
$this->assertSoftDeleted($post);
```

---

## HTTP Test Assertions

```php
// Status codes
$response->assertOk();           // 200
$response->assertCreated();      // 201
$response->assertAccepted();     // 202
$response->assertNoContent();    // 204
$response->assertBadRequest();   // 400
$response->assertUnauthorized(); // 401
$response->assertForbidden();    // 403
$response->assertNotFound();     // 404
$response->assertUnprocessable();// 422

// JSON assertions
$response->assertJson(['message' => 'Success']);
$response->assertJsonPath('data.user.name', 'John');
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonCount(5, 'data');
$response->assertJsonFragment(['status' => 'active']);

// Validation errors
$response->assertJsonValidationErrors(['email', 'password']);
$response->assertJsonMissingValidationErrors(['name']);

// Session assertions
$response->assertSessionHas('message', 'Success!');
$response->assertSessionHasErrors(['email']);
$response->assertSessionHasNoErrors();

// View assertions
$response->assertViewIs('posts.index');
$response->assertViewHas('posts');
$response->assertSee('Post Title');
$response->assertDontSee('Hidden Content');
```

---

## File Upload Testing

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

public function test_avatar_upload(): void
{
    Storage::fake('avatars');

    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

    $response = $this->post('/api/user/avatar', [
        'avatar' => $file,
    ]);

    Storage::disk('avatars')->assertExists($file->hashName());
}

public function test_document_upload(): void
{
    Storage::fake('documents');

    $file = UploadedFile::fake()
        ->create('document.pdf', 1000, 'application/pdf');

    $response = $this->post('/api/documents', [
        'document' => $file,
    ]);

    $response->assertOk();
}
```

---

## Exception Testing

```php
// PHPUnit style
public function test_throws_exception(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid value');
    $this->expectExceptionCode(100);

    throw new InvalidArgumentException('Invalid value', 100);
}

// Laravel style
public function test_exception_is_reported(): void
{
    Exceptions::fake();

    $response = $this->get('/api/error');

    Exceptions::assertReported(CustomException::class);
}

// AssertThrows
public function test_assert_throws(): void
{
    $this->assertThrows(
        fn() => (new PaymentService())->charge(-100),
        InvalidArgumentException::class
    );
}
```

---

## Time Manipulation

```php
use Illuminate\Support\Carbon;

public function test_expires_after_week(): void
{
    Carbon::setTestNow('2024-01-01 00:00:00');

    $link = InviteLink::create(['expires_at' => now()->addWeek()]);

    $this->travel(6)->days();
    $this->assertFalse($link->isExpired());

    $this->travel(1)->days();  // Now 7 days
    $this->assertTrue($link->isExpired());

    $this->travelBack(); // Reset time

    // Or with closure
    $this->travelTo(now()->addYear(), function () {
        // Test future behavior
    });
}
```

---

## Reference Files

For detailed patterns, see:
- `references/unit-testing-patterns.md` - Unit test organization and patterns
- `references/mocking-guide.md` - Complete mocking reference
- `references/api-testing.md` - REST API testing strategies
- `references/data-providers.md` - Advanced data provider patterns
- `references/assertions-cheatsheet.md` - Complete assertion reference

---

## Test Templates

Copy-ready test templates for common scenarios:
- `templates/UnitServiceTest.php` - Unit test template for service classes
- `templates/UnitModelTest.php` - Unit test template for Eloquent models
- `templates/FeatureApiTest.php` - Feature test template for API controllers
- `templates/FeatureAuthTest.php` - Feature test template for authentication
- `templates/PestComparison.php` - PHPUnit vs Pest PHP comparison guide

---

## Common Commands

```bash
# Create test
php artisan make:test Feature/PostControllerTest
php artisan make:test Unit/Services/PaymentServiceTest --unit

# Run with filter
php artisan test --filter="UserTest"
php artisan test --filter="test_user_can"

# Run by path
php artisan test tests/Feature/Api

# Parallel execution
php artisan test --parallel --processes=4

# Stop on failure
php artisan test --stop-on-failure

# Verbose output
php artisan test -v

# Coverage report
php artisan test --coverage-html=coverage
```

---

## Best Practices Checklist

- [ ] One assertion concept per test (but multiple assertions OK)
- [ ] Descriptive test names: `test_user_can_login_with_valid_credentials`
- [ ] Use `$this->actingAs($user)` for authenticated requests
- [ ] Prefer `postJson` for API tests
- [ ] Use factories for test data
- [ ] Mock external services and API calls
- [ ] Use data providers for edge cases
- [ ] Keep unit tests isolated (no database/framework)
- [ ] Use RefreshDatabase for database tests
- [ ] Test both happy path and error cases
- [ ] Assert JSON structure, not just values
- [ ] Use `$response->dump()` for debugging

### Infrastructure

- **Queue worker**: Docker service processes Redis-backed jobs (mail, TTS, etc.).
- **TTS**: `TTSServiceInterface` / `TTSService` for generated phrase audio.
- **Transcription**: `EspokeTranscriptionService` where phrase transcription is filled or regenerated.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context
This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.18
- laravel/cashier (CASHIER) - v16
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/socialite (SOCIALITE) - v5
- larastan/larastan (LARASTAN) - v3
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v3
- phpunit/phpunit (PHPUNIT) - v11
- tailwindcss (TAILWINDCSS) - v4

## Conventions
- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts
- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture
- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling
- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies
- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files
- You must only create documentation files if explicitly requested by the user.


=== boost rules ===

## Laravel Boost
- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan
- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs
- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging
- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool
- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)
- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax
- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms


=== php rules ===

## PHP

- Always use strict typing at the head of a `.php` file: `declare(strict_types=1);`.
- Always use curly braces for control structures, even if it has one line.

### Constructors
- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function __construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations
- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments
- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks
- Add useful array shape type definitions for arrays when appropriate.

## Enums
- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.


=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database
- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation
- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources
- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation
- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues
- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization
- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation
- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration
- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing
- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error
- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.


=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure
- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database
- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models
- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.


=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.


=== pest/core rules ===

## Pest
### Testing
- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests
- All tests must be written using Pest. Use `php artisan make:test --pest {name}`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
<code-snippet name="Basic Pest Test Example" lang="php">
it('is true', function () {
    expect(true)->toBeTrue();
});
</code-snippet>

### Running Tests
- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions
- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
<code-snippet name="Pest Example Asserting postJson Response" lang="php">
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);

    $response->assertSuccessful();
});
</code-snippet>

### Mocking
- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets
- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>


=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing
- When listing items, use gap utilities for spacing, don't use margins.

    <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
        <div class="flex gap-8">
            <div>Superior</div>
            <div>Michigan</div>
            <div>Erie</div>
        </div>
    </code-snippet>


### Dark Mode
- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.


=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, configuration is CSS-first using the `@theme` directive — no separate `tailwind.config.js` file is needed.
<code-snippet name="Extending Theme in CSS" lang="css">
@theme {
  --color-brand: oklch(0.72 0.11 178);
}
</code-snippet>

- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>


### Replaced Utilities
- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated |	Replacement |
|------------+--------------|
| bg-opacity-* | bg-black/* |
| text-opacity-* | text-black/* |
| border-opacity-* | border-black/* |
| divide-opacity-* | divide-black/* |
| ring-opacity-* | ring-black/* |
| placeholder-opacity-* | placeholder-black/* |
| flex-shrink-* | shrink-* |
| flex-grow-* | grow-* |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |
</laravel-boost-guidelines>

To working with artisan or related use:

docker exec -it php-fpm php artisan <artisan-command>
docker exec -it php-fpm <command>

