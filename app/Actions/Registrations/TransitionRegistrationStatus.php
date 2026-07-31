<?php

namespace App\Actions\Registrations;

use App\Models\Registration;
use App\RegistrationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransitionRegistrationStatus
{
    public function handle(Registration $registration, RegistrationStatus $nextStatus): Registration
    {
        return DB::transaction(function () use ($registration, $nextStatus): Registration {
            $lockedRegistration = Registration::query()->lockForUpdate()->findOrFail($registration->id);

            if (! in_array($nextStatus, $lockedRegistration->statusEnum()->availableTransitions(), true)) {
                throw ValidationException::withMessages([
                    'status' => 'That status transition is not available.',
                ]);
            }

            $lockedRegistration->update([
                'status' => $nextStatus,
                ...$this->timestampChanges($nextStatus),
            ]);

            return $lockedRegistration->refresh();
        });
    }

    /** @return array<string, mixed> */
    private function timestampChanges(RegistrationStatus $status): array
    {
        return match ($status) {
            RegistrationStatus::TestDriveScheduled => ['test_drive_scheduled_at' => now()],
            RegistrationStatus::TestDriveCompleted => ['test_drive_completed_at' => now()],
            RegistrationStatus::Purchased => ['purchased_at' => now()],
            RegistrationStatus::Cancelled => ['cancelled_at' => now()],
            RegistrationStatus::Registered => [],
        };
    }
}
