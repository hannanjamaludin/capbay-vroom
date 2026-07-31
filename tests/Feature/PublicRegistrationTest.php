<?php

use App\Livewire\Registration\CreateRegistration;
use App\Models\Registration;
use App\Models\Vehicle;
use Livewire\Livewire;

function activeVehicleForRegistration(): Vehicle
{
    return Vehicle::query()->create([
        'name' => 'Vroom One',
        'price_sen' => 10_000_000,
        'is_active' => true,
    ]);
}

test('the public registration page is available', function () {
    $this->get(route('registrations.create'))
        ->assertSuccessful()
        ->assertSee('Register your interest');
});

test('a customer can register and receives confirmation', function () {
    $vehicle = activeVehicleForRegistration();

    Livewire::test(CreateRegistration::class)
        ->set('form.customerName', '  Ada   Lovelace ')
        ->set('form.email', ' ADA@EXAMPLE.COM ')
        ->set('form.phone', '+60 12-345 6789')
        ->set('form.vehicleId', (string) $vehicle->id)
        ->set('form.downPayment', '10000.50')
        ->call('register')
        ->assertHasNoErrors()
        ->assertSet('registrationComplete', true)
        ->assertSee('Registration received');

    $registration = Registration::query()->sole();

    expect($registration->customer_name)->toBe('Ada Lovelace')
        ->and($registration->email)->toBe('ada@example.com')
        ->and($registration->phone)->toBe('+60123456789')
        ->and($registration->vehicle_id)->toBe($vehicle->id)
        ->and($registration->down_payment_sen)->toBe(1_000_050)
        ->and($registration->status)->toBe('registered')
        ->and($registration->registered_at)->not->toBeNull()
        ->and($registration->vehicle_price_sen)->toBe($vehicle->price_sen)
        ->and($registration->final_price_sen)->toBe($vehicle->price_sen);
});

test('invalid email and phone values are rejected', function (string $field, string $value) {
    $vehicle = activeVehicleForRegistration();

    Livewire::test(CreateRegistration::class)
        ->set('form.customerName', 'Ada Lovelace')
        ->set('form.email', 'ada@example.com')
        ->set('form.phone', '+60123456789')
        ->set('form.vehicleId', (string) $vehicle->id)
        ->set('form.downPayment', '10000')
        ->set($field, $value)
        ->call('register')
        ->assertHasErrors($field);

    expect(Registration::query()->count())->toBe(0);
})->with([
    'invalid email' => ['form.email', 'not-an-email'],
    'invalid phone' => ['form.phone', 'phone-me'],
]);

test('duplicate email and phone values are rejected', function (string $field, string $value) {
    $vehicle = activeVehicleForRegistration();

    Registration::factory()->create([
        'vehicle_id' => $vehicle->id,
        'email' => 'existing@example.com',
        'phone' => '+60123456789',
    ]);

    Livewire::test(CreateRegistration::class)
        ->set('form.customerName', 'Grace Hopper')
        ->set('form.email', 'new@example.com')
        ->set('form.phone', '+60111111111')
        ->set('form.vehicleId', (string) $vehicle->id)
        ->set('form.downPayment', '10000')
        ->set($field, $value)
        ->call('register')
        ->assertHasErrors([$field => ['unique']]);

    expect(Registration::query()->count())->toBe(1);
})->with([
    'duplicate email, regardless of case' => ['form.email', ' EXISTING@EXAMPLE.COM '],
    'duplicate normalized phone' => ['form.phone', '+60 12-345 6789'],
]);

test('an inactive vehicle cannot be registered', function () {
    $vehicle = Vehicle::query()->create([
        'name' => 'Unavailable Vroom',
        'price_sen' => 10_000_000,
        'is_active' => false,
    ]);

    Livewire::test(CreateRegistration::class)
        ->set('form.customerName', 'Ada Lovelace')
        ->set('form.email', 'ada@example.com')
        ->set('form.phone', '+60123456789')
        ->set('form.vehicleId', (string) $vehicle->id)
        ->set('form.downPayment', '10000')
        ->call('register')
        ->assertHasErrors('form.vehicleId');
});
