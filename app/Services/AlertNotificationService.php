<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\FinancialAlertNotification;
use Illuminate\Support\Collection;

class AlertNotificationService
{
    /**
     * @return Collection<int, User>
     */
    public function usersWithPermission(string $permissionSlug): Collection
    {
        return User::whereHas('roles.permissions', fn ($q) => $q->where('slug', $permissionSlug))
            ->orWhereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))
            ->get()
            ->unique('id');
    }

    public function notifyOncePerDay(User $user, string $alertType, string $title, string $message, ?string $url = null): void
    {
        $alreadySent = $user->notifications()
            ->where('data->alert_type', $alertType)
            ->where('data->message', $message)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadySent) {
            return;
        }

        $user->notify(new FinancialAlertNotification($alertType, $title, $message, $url));
    }
}
