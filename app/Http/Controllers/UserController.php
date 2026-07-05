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
        $mechanics = $this->userService->paginatedMechanics(
            $request->query('q'),
            (int) $request->query('per_page', 10)
        );

        return $this->sendSuccess(UserResource::collection($mechanics)->response()->getData(true));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $mechanic = $this->userService->createMechanic($request->validated());

        return $this->sendSuccess(new UserResource($mechanic), 'Mechanic created successfully.', 201);
    }

    public function update(UpdateUserRequest $request, User $mechanic): JsonResponse
    {
        $mechanic = $this->userService->updateMechanic($mechanic, $request->validated());

        return $this->sendSuccess(new UserResource($mechanic), 'Mechanic updated successfully.');
    }

    public function destroy(User $mechanic): JsonResponse
    {
        if (! $this->userService->deleteMechanic($mechanic)) {
            return $this->sendError('Only mechanic accounts can be deleted.', 422);
        }

        return $this->sendSuccess(null, 'Mechanic deleted successfully.');
    }
}
