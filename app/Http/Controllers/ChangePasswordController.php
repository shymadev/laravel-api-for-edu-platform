<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ChangePasswordDTO;
use App\Services\ChangePasswordProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use RuntimeException;

/**
 * Controller responsible for handling user password change requests.
 */
class ChangePasswordController extends Controller
{
    /**
     * Constructs a new ChangePasswordController instance.
     *
     * @param \App\Services\ChangePasswordProcessor $changePasswordProcessor
     *
     * @return void
     */
    public function __construct(
        protected readonly ChangePasswordProcessor $changePasswordProcessor,
    ) {
    }

    /**
     * Handles the password change request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User\User $user */
            $user = $request->user();

            $request->validate([
                'old_password' => [
                    Rule::requiredIf(fn (): bool => $request->user()->hasLocalPassword()),
                    'nullable',
                    'string',
                ],
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $result = $this->changePasswordProcessor->process(new ChangePasswordDTO(
                user: $request->user(),
                oldPassword: $request->input('old_password'),
                newPassword: $request->input('new_password'),
            ));

            if ($result) {
                $this->logSuccessfulPasswordChange($request);
            } else {
                $this->logFailedPasswordChange($request, new RuntimeException('Password change failed without exception'));
            }

            return $result ? response()->json(['message' => 'Password changed successfully'])
                : response()->json(['message' => 'Failed to change password'], 400);
        } catch (RuntimeException $e) {
            $this->logFailedPasswordChange($request, $e);

            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\InvalidArgumentException $e) {
            $this->logFailedPasswordChange($request, $e);

            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $this->logFailedPasswordChange($request, $e);

            return response()->json(['message' => 'An error occurred while changing password'], 500);
        }
    }

    /**
     * Logs a successful password change event.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    private function logSuccessfulPasswordChange(Request $request): void
    {
        Log::channel('db')->info('Password changed successfully', [
            'user_id' => $request->user()->id,
            'action' => 'password_change_success',
        ]);
    }

    /**
     * Logs a failed password change event.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     *
     * @return void
     */
    private function logFailedPasswordChange(Request $request, \Throwable $e): void
    {
        Log::channel('db')->error('Failed to change password', [
            'user_id' => $request->user()->id,
            'action' => 'password_change_failed',
            'exception' => $e,
        ]);
    }
}
