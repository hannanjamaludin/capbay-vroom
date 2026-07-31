<?php

use App\Livewire\Agent\RegistrationIndex;
use App\Livewire\Agent\RegistrationShow;
use App\Models\Promotion;
use App\Models\Registration;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

test('the landing page links to the sales agent login', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee(route('agent.login'));
});

test('a sales agent can log in and log out', function () {
    $agent = User::factory()->salesAgent()->create([
        'email' => 'agent@example.com',
        'password' => 'secret-password',
    ]);

    $this->post(route('agent.login.store'), [
        'email' => $agent->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('agent.registrations.index'));

    $this->assertAuthenticatedAs($agent);

    $this->post(route('agent.logout'))
        ->assertRedirect(route('agent.login'));

    $this->assertGuest();
});

test('non agents cannot authenticate through the sales portal', function () {
    $admin = User::factory()->admin()->create([
        'password' => 'secret-password',
    ]);

    $this->post(route('agent.login.store'), [
        'email' => $admin->email,
        'password' => 'secret-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('agent registration routes are protected by authentication and role', function () {
    $registration = Registration::factory()->create();

    $this->get(route('agent.registrations.index'))
        ->assertRedirect(route('agent.login'));

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('agent.registrations.show', $registration))
        ->assertForbidden();
});

test('agents can search and filter the paginated registration list', function () {
    $agent = User::factory()->salesAgent()->create();
    $firstVehicle = Vehicle::query()->create([
        'name' => 'Vroom Alpha',
        'price_sen' => 10_000_000,
        'is_active' => true,
    ]);
    $secondVehicle = Vehicle::query()->create([
        'name' => 'Vroom Beta',
        'price_sen' => 12_000_000,
        'is_active' => true,
    ]);

    Registration::factory()->create([
        'vehicle_id' => $firstVehicle->id,
        'customer_name' => 'Searchable Customer',
        'email' => 'searchable@example.com',
        'status' => 'registered',
    ]);
    Registration::factory()->testDriveCompleted()->create([
        'vehicle_id' => $secondVehicle->id,
        'customer_name' => 'Hidden Customer',
        'email' => 'hidden@example.com',
    ]);

    Livewire::actingAs($agent)
        ->test(RegistrationIndex::class)
        ->set('search', 'Searchable')
        ->assertSee('Searchable Customer')
        ->assertDontSee('Hidden Customer')
        ->set('search', '')
        ->set('status', 'test_drive_completed')
        ->set('vehicle', (string) $secondVehicle->id)
        ->assertSee('Hidden Customer')
        ->assertDontSee('Searchable Customer')
        ->assertSee('1 - 1')
        ->assertSee('out of 1')
        ->assertViewHas('registrations', fn ($registrations): bool => $registrations instanceof \Illuminate\Pagination\LengthAwarePaginator);
});

test('the registration footer shows the current indexes and filtered total on every page', function () {
    $agent = User::factory()->salesAgent()->create();
    $vehicle = Vehicle::query()->create([
        'name' => 'Paginated Vroom',
        'price_sen' => 10_000_000,
        'is_active' => true,
    ]);

    Registration::factory()->count(15)->create([
        'vehicle_id' => $vehicle->id,
        'status' => 'registered',
    ]);
    Registration::factory()->testDriveCompleted()->create([
        'vehicle_id' => $vehicle->id,
    ]);

    Livewire::actingAs($agent)
        ->test(RegistrationIndex::class)
        ->set('status', 'registered')
        ->call('setPage', 2)
        ->assertSee('13 - 15')
        ->assertSee('out of 15');
});

test('agents can update financials with an already approved loan assumption', function () {
    $agent = User::factory()->salesAgent()->create();
    $vehicle = Vehicle::query()->create([
        'name' => 'Finance Vroom',
        'price_sen' => 20_000_000,
        'is_active' => true,
    ]);
    $promotion = Promotion::query()->create([
        'vehicle_id' => $vehicle->id,
        'name' => 'Agent Promotion',
        'discount_basis_points' => 1000,
        'minimum_down_payment_basis_point' => 1000,
        'customer_limit' => 10,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'is_active' => true,
    ]);
    $registration = Registration::factory()->create([
        'vehicle_id' => $vehicle->id,
        'promotion_id' => null,
        'email' => 'finance@example.com',
        'down_payment_sen' => 1_000_000,
        'vehicle_price_sen' => 20_000_000,
        'final_price_sen' => 20_000_000,
        'loan_amount_sen' => 19_000_000,
    ]);

    Livewire::actingAs($agent)
        ->test(RegistrationShow::class, ['registration' => $registration])
        ->set('downPayment', '20000.00')
        ->call('updateFinancials')
        ->assertHasNoErrors()
        ->assertSee('Promotion eligible');

    $registration->refresh();

    expect($registration->promotion_id)->toBe($promotion->id)
        ->and($registration->applied_discount_sen)->toBe(2_000_000)
        ->and($registration->final_price_sen)->toBe(18_000_000)
        ->and($registration->loan_amount_sen)->toBe(16_000_000);
});

test('agents can only move registrations through valid status transitions', function () {
    $agent = User::factory()->salesAgent()->create();
    $registration = Registration::factory()->create();

    $component = Livewire::actingAs($agent)
        ->test(RegistrationShow::class, ['registration' => $registration])
        ->call('updateStatus', 'test_drive_scheduled')
        ->assertHasNoErrors()
        ->call('updateStatus', 'test_drive_completed')
        ->assertHasNoErrors()
        ->call('updateStatus', 'purchased')
        ->assertHasNoErrors();

    $registration->refresh();

    expect($registration->status)->toBe('purchased')
        ->and($registration->test_drive_scheduled_at)->not->toBeNull()
        ->and($registration->test_drive_completed_at)->not->toBeNull()
        ->and($registration->purchased_at)->not->toBeNull();

    $component->call('updateStatus', 'cancelled')->assertHasErrors('status');
});
