<?php

namespace App;

enum RegistrationStatus: string
{
    case Registered = 'registered';
    case TestDriveScheduled = 'test_drive_scheduled';
    case TestDriveCompleted = 'test_drive_completed';
    case Purchased = 'purchased';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registered',
            self::TestDriveScheduled => 'Test drive scheduled',
            self::TestDriveCompleted => 'Test drive completed',
            self::Purchased => 'Purchased',
            self::Cancelled => 'Cancelled',
        };
    }

    /** @return list<self> */
    public function availableTransitions(): array
    {
        return match ($this) {
            self::Registered => [self::TestDriveScheduled, self::Cancelled],
            self::TestDriveScheduled => [self::TestDriveCompleted, self::Cancelled],
            self::TestDriveCompleted => [self::Purchased, self::Cancelled],
            self::Purchased, self::Cancelled => [],
        };
    }
}
