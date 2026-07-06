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

        return $user;
    }

    public function paginatedMechanics(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return User::where('role', 'mechanic')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createMechanic(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'mechanic',
        ]);
    }

    public function updateMechanic(User $mechanic, array $data): User
    {
        $mechanic->name = $data['name'];
        $mechanic->email = $data['email'];

        if (! empty($data['password'])) {
            $mechanic->password = $data['password'];
        }

        $mechanic->save();

        return $mechanic;
    }

    public function deleteMechanic(User $mechanic): bool
    {
        if ($mechanic->role !== 'mechanic') {
            return false;
        }

        $mechanic->tokens()->delete();
        $mechanic->delete();

        return true;
    }
}
