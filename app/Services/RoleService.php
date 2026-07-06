<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleService
{
    public function paginated(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Role::query()
            ->with('permissions')
            ->withCount('users')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Role
    {
        $role = Role::create(['name' => $data['name']]);
        $role->permissions()->sync($data['permissions']);

        return $role->load('permissions')->loadCount('users');
    }

    public function update(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);
        $role->permissions()->sync($data['permissions']);

        return $role->load('permissions')->loadCount('users');
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function delete(Role $role): array
    {
        if ($role->users()->exists()) {
            return ['ok' => false, 'message' => 'This role is assigned to users and cannot be deleted.'];
        }

        $role->permissions()->detach();
        $role->delete();

        return ['ok' => true, 'message' => 'Role deleted successfully.'];
    }
}
