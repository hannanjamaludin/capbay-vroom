<?php

use App\Models\Registration;

test('registration factory states represent each workflow transition', function (
    string $state,
    string $expectedStatus,
    array $populatedTimestamps,
) {
    $registration = Registration::factory()->{$state}()->create();

    expect($registration->status)->toBe($expectedStatus);

    foreach ($populatedTimestamps as $timestamp) {
        expect($registration->{$timestamp})->not->toBeNull();
    }
})->with([
    'test drive scheduled' => [
        'testDriveScheduled',
        'test_drive_scheduled',
        ['test_drive_scheduled_at'],
    ],
    'test drive completed' => [
        'testDriveCompleted',
        'test_drive_completed',
        ['test_drive_scheduled_at', 'test_drive_completed_at'],
    ],
    'purchased' => [
        'purchased',
        'purchased',
        [
            'test_drive_scheduled_at',
            'test_drive_completed_at',
            'loan_approved_at',
            'purchased_at',
        ],
    ],
    'cancelled' => [
        'cancelled',
        'cancelled',
        ['cancelled_at'],
    ],
]);

test('a new registration starts in the registered state without transition timestamps', function () {
    $registration = Registration::factory()->create();

    expect($registration->status)->toBe('registered')
        ->and($registration->registered_at)->not->toBeNull()
        ->and($registration->test_drive_scheduled_at)->toBeNull()
        ->and($registration->test_drive_completed_at)->toBeNull()
        ->and($registration->loan_approved_at)->toBeNull()
        ->and($registration->purchased_at)->toBeNull()
        ->and($registration->cancelled_at)->toBeNull();
});
