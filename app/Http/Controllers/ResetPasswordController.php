<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ForgotPasswordDTO;
use App\DTO\ResetPasswordDTO;
use App\Services\ForgotPasswordProcessor;
use App\Services\ResetPasswordProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

/**
 * Controller that handles password reset operations.
 */
class ResetPasswordController extends Controller
{
    /**
     * Constructs a new ResetPasswordController instance.
     *
     * @param \App\Services\ResetPasswordProcessor $resetPasswordProcessor
     * @param \App\Services\ForgotPasswordProcessor $forgotPasswordProcessor
     *
     * @return void
     */
    public function __construct(
        protected readonly ResetPasswordProcessor $resetPasswordProcessor,
        protected readonly ForgotPasswordProcessor $forgotPasswordProcessor,
    ) {
    }

    /**
     * Handles the forgot password request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->forgotPasswordProcessor->process(new ForgotPasswordDTO(
            email: $request->input('email'),
        ));

        $this->logForgotPasswordResult($request, $status);

        $neutralMessage = 'If an account exists for this email, you will receive password reset instructions.';

        return match ($status) {
            Password::RESET_LINK_SENT,
            Password::INVALID_USER => response()->json(['message' => $neutralMessage]),
            Password::RESET_THROTTLED => response()->json([
                'message' => 'Too many password reset attempts. Please try again later.',
                'status' => $status,
            ], 429),
            default => response()->json([
                'message' => 'Unable to process password reset request.',
                'status' => $status,
            ], 500),
        };
    }

    /**
     * Handles the password reset request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = $this->resetPasswordProcessor->process(new ResetPasswordDTO(
            email: $request->input('email'),
            token: $request->input('token'),
            password: $request->input('password'),
            passwordConfirmation: $request->input('password_confirmation'),
        ));

        $this->logResetPasswordResult($request, $status);

        $invalidMessage = 'Invalid or expired reset link. Please request a new password reset.';

        return match ($status) {
            Password::PASSWORD_RESET => response()->json([
                'message' => 'Password has been reset successfully.',
            ]),
            Password::INVALID_TOKEN,
            Password::INVALID_USER => response()->json([
                'message' => $invalidMessage,
                'status' => $status,
            ], 422),
            default => response()->json([
                'message' => 'Unable to reset password.',
                'status' => $status,
            ], 500),
        };
    }

    /**
     * Logs the result of a forgot password request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $status
     *
     * @return void
     */
    private function logForgotPasswordResult(Request $request, string $status): void
    {
        $context = [
            'action' => 'forgot_password',
            'status' => $status,
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ];

        match ($status) {
            Password::RESET_LINK_SENT => Log::channel('db')->info('Password reset link sent', $context),
            Password::INVALID_USER => Log::channel('db')->info('Password reset requested for unknown email', $context),
            Password::RESET_THROTTLED => Log::channel('db')->warning('Password reset link throttled', $context),
            default => Log::channel('db')->error('Password reset link request failed', $context),
        };
    }

    /**
     * Logs the result of a password reset attempt.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $status
     *
     * @return void
     */
    private function logResetPasswordResult(Request $request, string $status): void
    {
        $context = [
            'action' => 'reset_password',
            'status' => $status,
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ];

        match ($status) {
            Password::PASSWORD_RESET => null,
            Password::INVALID_TOKEN,
            Password::INVALID_USER => Log::channel('db')->warning('Password reset rejected', $context),
            default => Log::channel('db')->error('Password reset failed unexpectedly', $context),
        };
    }
}
