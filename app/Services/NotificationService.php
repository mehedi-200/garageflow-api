<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function paginatedFor(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Notification::where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function unreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)->whereNull('read_at')->count();
    }

    public function markAllRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function notifyUser(int $userId, string $message, ?string $link = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'message' => $message,
            'link' => $link,
        ]);
    }

    public function notifyAdmins(string $message, ?string $link = null, ?int $exceptUserId = null): void
    {
        User::where('is_admin', true)
            ->when($exceptUserId, fn ($q) => $q->where('id', '!=', $exceptUserId))
            ->pluck('id')
            ->each(fn ($id) => $this->notifyUser($id, $message, $link));
    }
}
