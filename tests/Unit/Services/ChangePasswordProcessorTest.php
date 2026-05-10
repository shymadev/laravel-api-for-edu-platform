<?php

declare(strict_types=1);

/**
 * Unit tests for ChangePasswordProcessor and change-password strategies.
 */

use App\DTO\ChangePasswordDTO;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\ChangePasswordProcessor;
use App\Services\Strategy\ChangePassword\GoogleChangePasswordStrategy;
use App\Services\Strategy\ChangePassword\StandardChangePasswordStrategy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    Role::upsert(
        [['id' => Role::USER_ROLE_ID, 'role_name' => 'user']],
        ['id'],
    );

    $this->user = User::create([
        'username' => 'pw_user',
        'email' => 'pw@example.com',
        'password_hash' => Hash::make('oldpassword'),
        'role_id' => Role::USER_ROLE_ID,
    ]);
});

/**
 * process() uses the first strategy that supports the DTO, or throws if none match.
 */
it('test_process', function (bool $hasMatchingStrategy): void {
    $dto = new ChangePasswordDTO(user: $this->user, oldPassword: 'oldpassword', newPassword: 'newpassword99');

    if ($hasMatchingStrategy) {
        Event::fake();

        $strategy = Mockery::mock(\App\Services\Strategy\ChangePassword\ChangePasswordStrategy::class);
        $strategy->shouldReceive('supports')->once()->andReturn(true);
        $strategy->shouldReceive('handle')->once()->andReturn(true);

        $processor = new ChangePasswordProcessor([$strategy]);

        expect($processor->process($dto))->toBeTrue();
        Event::assertDispatched(\App\Events\UserPasswordChanged::class);
    } else {
        $strategy = Mockery::mock(\App\Services\Strategy\ChangePassword\ChangePasswordStrategy::class);
        $strategy->shouldReceive('supports')->once()->andReturn(false);

        $processor = new ChangePasswordProcessor([$strategy]);

        expect(fn () => $processor->process($dto))
            ->toThrow(\RuntimeException::class);
    }
})->with(dataProviderForTestProcess());

/**
 * Provides strategy matching flags for testForProcess.
 */
function dataProviderForTestProcess(): array
{
    return [
        'matching strategy → delegates and returns result' => [true],
        'no matching strategy → throws RuntimeException' => [false],
    ];
}

/**
 * StandardChangePasswordStrategy::supports — true for local users, false for Google-only.
 */
it('test_standard_supports', function (bool $isGoogleOnly, bool $expected): void {
    if ($isGoogleOnly) {
        $this->user->google_id = 'google_123';
        $this->user->password_hash = '';
        $this->user->save();
    }

    $dto = new ChangePasswordDTO(user: $this->user->fresh(), oldPassword: null, newPassword: 'new');
    $strategy = new StandardChangePasswordStrategy();

    expect($strategy->supports($dto))->toBe($expected);
})->with(dataProviderForTestStandardSupports());

/**
 * Provides Google-only flags and expected support results for testForStandardSupports.
 */
function dataProviderForTestStandardSupports(): array
{
    return [
        'standard user → supports' => [false, true],
        'Google-only user → does not support' => [true, false],
    ];
}

/**
 * StandardChangePasswordStrategy::handle returns false when the old password is wrong.
 */
it('test_standard_handle_wrong_old_password', function (): void {
    $dto = new ChangePasswordDTO(user: $this->user, oldPassword: 'wrongpassword', newPassword: 'newpassword99');

    $result = (new StandardChangePasswordStrategy())->handle($dto);

    expect($result)->toBeFalse();
});

/**
 * StandardChangePasswordStrategy::handle throws when new password equals the old one.
 */
it('test_standard_handle_same_password', function (): void {
    $dto = new ChangePasswordDTO(user: $this->user, oldPassword: 'oldpassword', newPassword: 'oldpassword');

    expect(fn () => (new StandardChangePasswordStrategy())->handle($dto))
        ->toThrow(\InvalidArgumentException::class, 'New password cannot be the same as the old password.');
});

/**
 * StandardChangePasswordStrategy::handle updates the hash and returns true on success.
 */
it('test_standard_handle_success', function (): void {
    $dto = new ChangePasswordDTO(user: $this->user, oldPassword: 'oldpassword', newPassword: 'newpassword99');

    $result = (new StandardChangePasswordStrategy())->handle($dto);

    expect($result)->toBeTrue()
        ->and(Hash::check('newpassword99', User::find($this->user->id)->password_hash))->toBeTrue();
});

/**
 * GoogleChangePasswordStrategy::supports — true only for Google-only accounts.
 */
it('test_google_supports', function (bool $isGoogleOnly, bool $expected): void {
    if ($isGoogleOnly) {
        $this->user->google_id = 'google_123';
        $this->user->password_hash = '';
        $this->user->save();
    }

    $dto = new ChangePasswordDTO(user: $this->user->fresh(), oldPassword: null, newPassword: 'new');
    $strategy = new GoogleChangePasswordStrategy();

    expect($strategy->supports($dto))->toBe($expected);
})->with(dataProviderForTestGoogleSupports());

/**
 * Provides Google-only flags and expected support results for testForGoogleSupports.
 */
function dataProviderForTestGoogleSupports(): array
{
    return [
        'Google-only user → supports' => [true, true],
        'standard user → does not support' => [false, false],
    ];
}

/**
 * GoogleChangePasswordStrategy::handle sets a new password without the old one.
 */
it('test_google_handle', function (): void {
    $this->user->google_id = 'google_123';
    $this->user->password_hash = '';
    $this->user->save();

    $dto = new ChangePasswordDTO(user: $this->user->fresh(), oldPassword: null, newPassword: 'brandnewpass');
    $result = (new GoogleChangePasswordStrategy())->handle($dto);

    expect($result)->toBeTrue()
        ->and(Hash::check('brandnewpass', User::find($this->user->id)->password_hash))->toBeTrue();
});
