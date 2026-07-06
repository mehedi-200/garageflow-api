<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->paginatedUsers(
            $request->query('q'),
            (int) $request->query('per_page', 10)
        );

        return $this->sendSuccess(UserResource::collection($users)->response()->getData(true));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return $this->sendSuccess(new UserResource($user), 'User created successfully.', 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $result = $this->userService->updateUser($user, $request->validated());

        if (! $result['ok']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendSuccess(new UserResource($result['user']), $result['message']);
    }

    public function destroy(User $user): JsonResponse
    {
        $result = $this->userService->deleteUser($user);

        if (! $result['ok']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendSuccess(null, $result['message']);
    }
}
