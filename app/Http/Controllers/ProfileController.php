<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\User\ProfileResource;
use App\Services\UserProfileService;
use Illuminate\Database\RecordNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller that handles user profile operations.
 */
class ProfileController extends Controller
{
    /**
     * Construct a new ProfileController instance.
     *
     * @param \App\Services\UserProfileService $userProfileService
     *
     * @return void
     */
    public function __construct(protected readonly UserProfileService $userProfileService)
    {
    }

    /**
     * Retrieve a user profile by ID.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\User\ProfileResource
     */
    public function getProfileById(int $id, Request $request): JsonResponse|ProfileResource
    {
        try {
            $user = $request->user();

            if ($user->profile_id !== $id) {
                return response()->json([
                    'message' => 'Unauthorized access to profile',
                ], 403);
            }

            $profile = $this->userProfileService->getProfileById($id);

            return new ProfileResource($profile);
        } catch (RecordNotFoundException) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to retrieve profile', [
                'profile_id' => $id,
                'action' => 'profile_show_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to retrieve profile'], 500);
        }
    }

    /**
     * Update a user profile by ID.
     *
     * @param \App\Http\Requests\UpdateProfileRequest $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfileById(UpdateProfileRequest $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->profile_id !== $id) {
                return response()->json([
                    'message' => 'Unauthorized access to profile',
                ], 403);
            }

            $profileToUpdate = $this->userProfileService->getProfileById($id);
            $this->userProfileService->updateProfile($profileToUpdate, $request->toDTO());

            Log::channel('db')->info('Profile updated', [
                'profile_id' => $id,
                'user_id' => $user->id,
                'action' => 'profile_update',
            ]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile' => new ProfileResource($profileToUpdate),
            ]);
        } catch (RecordNotFoundException) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to update profile', [
                'profile_id' => $id,
                'action' => 'profile_update_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to update profile'], 500);
        }
    }

    /**
     * Update a user's profile avatar.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfileAvatar(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->profile_id !== $id) {
                return response()->json([
                    'message' => 'Unauthorized access to profile',
                ], 403);
            }

            $profileToUpdate = $this->userProfileService->getProfileById($id);

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $this->userProfileService->updateProfileAvatar($file, $profileToUpdate);

                Log::channel('db')->info('Profile avatar updated', [
                    'profile_id' => $id,
                    'user_id' => $user->id,
                    'action' => 'profile_avatar_update',
                ]);
            }

            return response()->json([
                'message' => 'Profile avatar updated successfully',
                'profile' => new ProfileResource($profileToUpdate),
            ]);
        } catch (RecordNotFoundException) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to update profile avatar', [
                'profile_id' => $id,
                'action' => 'profile_avatar_update_failed',
                'exception' => $e,
            ]);

            return response()->json(['message' => 'Failed to update profile avatar'], 500);
        }
    }
}
