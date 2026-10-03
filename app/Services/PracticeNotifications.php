<?php

namespace App\Services;

use App\Enums\PracticeStatus;
use App\Enums\UserDepartment;
use App\Models\Practice;
use App\Models\User;
use App\Notifications\PracticeStatusChanged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class PracticeNotifications
{
    public static function recipients(Practice $practice): Collection
    {
        return User::role(['superadmin', UserDepartment::BACK_OFFICE->value])->get()
            ->push($practice->user)
            ->filter()
            ->unique('id');
    }

    public static function statusChanged(Practice $practice, PracticeStatus $oldStatus, PracticeStatus $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $actor = auth()->user();
        Notification::send(
            self::recipients($practice)->reject(fn (User $user) => $user->id === $actor?->id),
            new PracticeStatusChanged($practice, $oldStatus->getLabelText(), $newStatus->getLabelText(), $actor?->id, $actor?->full_name)
        );
    }
}
