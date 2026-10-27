<?php

namespace App\Services;

use App\Enums\PracticeStatus;
use App\Enums\UserDepartment;
use App\Models\Practice;
use App\Models\User;
use App\Notifications\PracticeCreated;
use App\Notifications\PracticeStatusChanged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PracticeNotifications
{
    public static function createdManually(Practice $practice): void
    {
        $notification = new PracticeCreated($practice, auth()->id(), auth()->user()?->full_name);

        // Only the interactive creation flow calls this, never Excel or the observer.
        // Defer the entire dispatch until the outermost transaction commits.
        DB::afterCommit(static function () use ($notification): void {
            Notification::send(
                User::role(UserDepartment::BACK_OFFICE->value)->get(),
                $notification
            );
        });
    }

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
