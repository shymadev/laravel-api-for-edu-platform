<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Entities\PaginationOptions;
use App\Http\Controllers\Entities\SearchOptions;
use App\Http\Controllers\Helpers\PaginatorTrait;
use App\Http\Controllers\Helpers\SearcherTrait;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Controller that handles user-related operations.
 */
class UserController extends Controller
{
    use PaginatorTrait;
    use SearcherTrait;

    /**
     * Constructs a new UserController instance.
     *
     * @param \App\Services\UserService $userService
     *
     * @return void
     */
    public function __construct(protected readonly UserService $userService)
    {
    }

    /**
     * Display a listing of users with optional pagination and search.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $paginationOptions = $this->extractPaginationOptions($request);
        $searchOptions = $this->extractSearchOptions($request);
        $query = User::query();

        if ($searchOptions instanceof SearchOptions) {
            $query = $this->addSearchConditions($query, $searchOptions, ['username', 'email']);
        }

        if ($paginationOptions instanceof PaginationOptions) {
            $users = $this->paginateQuery($query, $paginationOptions);

            return UserResource::collection($users);
        }

        return UserResource::collection($query->get());
    }

    /**
     * Store a newly created user in storage.
     *
     * @param \App\Http\Requests\User\CreateUserRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->toDTO());
            Log::info('User created', ['user_id' => $user->id]);

            return response()->json($user, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create user', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param int|string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int|string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if ($user === null) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(UserResource::make($user));
    }

    /**
     * Update the specified user in storage.
     *
     * @param \App\Http\Requests\User\UpdateUserRequest $request
     * @param \App\Models\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $user = $this->userService->updateUser($user, $request->toDTO());
            Log::info('User updated', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            Log::error('Failed to update user', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
            ], 500);
        }

        return UserResource::make($user)->response();
    }

    /**
     * Remove the specified user from storage.
     *
     * @param \App\Models\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $deletedBy = Auth::user()?->username;
            $deletionResult = $this->userService->deleteUser($user, $deletedBy);

            if (!$deletionResult) {
                Log::warning('Failed to delete user', ['user_id' => $user->id]);

                return response()->json(['message' => 'Failed to delete user'], 400);
            }

            Log::info('User deleted', ['user_id' => $user->id, 'deleted_by' => $deletedBy]);
        } catch (\Exception $e) {
            Log::error('Failed to delete user', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage(),
            ], 500);
        }

        return UserResource::make($user)->response()->setStatusCode(204);
    }

    /**
     * Block the specified user.
     *
     * @param \App\Models\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function block(User $user): JsonResponse
    {
        try {
            if (!$this->userService->isUserBlocked($user)) {
                $blockingResult = $this->userService->blockUser($user);

                if (!$blockingResult) {
                    Log::warning('Failed to block user', ['user_id' => $user->id]);

                    return response()->json(['message' => 'Failed to block user'], 400);
                }

                Log::info('User blocked', ['user_id' => $user->id]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to block user', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to block user',
                'error' => $e->getMessage(),
            ], 500);
        }

        return UserResource::make($user)->response();
    }

    /**
     * Unblock the specified user.
     *
     * @param \App\Models\User\User $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unblock(User $user): JsonResponse
    {
        try {
            if ($this->userService->isUserBlocked($user)) {
                $unblockingResult = $this->userService->unblockUser($user);

                if (!$unblockingResult) {
                    Log::warning('Failed to unblock user', ['user_id' => $user->id]);

                    return response()->json(['message' => 'Failed to unblock user'], 400);
                }

                Log::info('User unblocked', ['user_id' => $user->id]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to unblock user', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to unblock user',
                'error' => $e->getMessage(),
            ], 500);
        }

        return UserResource::make($user)->response();
    }
}
