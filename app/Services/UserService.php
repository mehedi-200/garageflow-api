<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function updateProfile(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user->load('role.permissions');
    }

    public function paginatedUsers(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->with('role.permissions')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['role_id'],
            'is_admin' => false,
        ])->load('role.permissions');
    }

    /**
     * @return array{ok: bool, message: string, user?: User}
     */
    public function updateUser(User $user, array $data): array
    {
        if ($user->is_admin) {
            return ['ok' => false, 'message' => 'Super admin accounts cannot be edited here.'];
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role_id = $data['role_id'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return ['ok' => true, 'message' => 'User updated successfully.', 'user' => $user->load('role.permissions')];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function deleteUser(User $user): array
    {
        if ($user->is_admin) {
            return ['ok' => false, 'message' => 'Super admin accounts cannot be deleted.'];
        }

        $user->tokens()->delete();
        $user->delete();

        return ['ok' => true, 'message' => 'User deleted successfully.'];
    }
}
