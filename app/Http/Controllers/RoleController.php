<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use App\Services\RoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly RoleService $roleService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleService->paginated(
            $request->query('q'),
            (int) $request->query('per_page', 10)
        );

        return $this->sendSuccess(RoleResource::collection($roles)->response()->getData(true));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return $this->sendSuccess(new RoleResource($role), 'Role created successfully.', 201);
    }

    public function show(Role $role): JsonResponse
    {
        return $this->sendSuccess(new RoleResource($role->load('permissions')->loadCount('users')));
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role = $this->roleService->update($role, $request->validated());

        return $this->sendSuccess(new RoleResource($role), 'Role updated successfully.');
    }

    public function destroy(Role $role): JsonResponse
    {
        $result = $this->roleService->delete($role);

        if (! $result['ok']) {
            return $this->sendError($result['message'], 422);
        }

        return $this->sendSuccess(null, $result['message']);
    }
}
