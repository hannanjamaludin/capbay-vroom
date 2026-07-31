<?php

use App\Models\Promotion;
use App\Models\Registration;
use App\Models\Vehicle;
use App\Services\RegistrationPricingService;

/**
 * @return array{
 *     vehicle: Vehicle,
 *     promotion: Promotion,
 *     pricingService: RegistrationPricingService
 * }
 */
function createRegistrationPricingScenario(): array
{
    $vehicle = Vehicle::query()->create([
        'name' => 'Test Vroom',
        'price_sen' => 20_000_000,
        'is_active' => true,
    ]);

    $promotion = Promotion::query()->create([
        'vehicle_id' => $vehicle->id,
        'name' => 'Test First 10',
        'discount_basis_points' => 1500,
        'minimum_down_payment_basis_point' => 1000,
        'customer_limit' => 10,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'is_active' => true,
    ]);

    return [
        'vehicle' => $vehicle,
        'promotion' => $promotion,
        'pricingService' => app(RegistrationPricingService::class),
    ];
}

test('it continues at full price when the down payment is ineligible', function () {
    $scenario = createRegistrationPricingScenario();
    $pricing = $scenario['pricingService']->calculate(
        $scenario['vehicle'],
        $scenario['promotion'],
        1_000_000,
        'customer.b@example.com',
    );

    expect($pricing['is_promotion_eligible'])->toBeFalse()
        ->and($pricing['promotion_id'])->toBeNull()
        ->and($pricing['applied_discount_sen'])->toBe(0)
        ->and($pricing['final_price_sen'])->toBe(20_000_000)
        ->and($pricing['loan_amount_sen'])->toBe(19_000_000)
        ->and($pricing['ineligibility_reason'])
        ->toBe('The down payment does not meet the promotion minimum.');
});

test('it rejects a promotion when eligibility requirements are not met', function (Closure $makeIneligible, string $reason) {
    $scenario = createRegistrationPricingScenario();
    $arguments = [
        'vehicle' => $scenario['vehicle'],
        'promotion' => $scenario['promotion'],
        'downPaymentSen' => 2_000_000,
        'customerEmail' => 'eligibility@example.com',
        'registeredAt' => now(),
    ];

    $makeIneligible($scenario, $arguments);

    $pricing = $scenario['pricingService']->calculate(...$arguments);

    expect($pricing['is_promotion_eligible'])->toBeFalse()
        ->and($pricing['promotion_id'])->toBeNull()
        ->and($pricing['applied_discount_sen'])->toBe(0)
        ->and($pricing['ineligibility_reason'])->toBe($reason);
})->with([
    'no promotion selected' => [
        function (array $scenario, array &$arguments): void {
            $arguments['promotion'] = null;
        },
        'No promotion was selected.',
    ],
    'inactive vehicle' => [
        function (array $scenario, array &$arguments): void {
            $scenario['vehicle']->update(['is_active' => false]);
        },
        'The vehicle is not active.',
    ],
    'promotion belongs to another vehicle' => [
        function (array $scenario, array &$arguments): void {
            $arguments['promotion']->update([
                'vehicle_id' => Vehicle::query()->create([
                    'name' => 'Another Vroom',
                    'price_sen' => 20_000_000,
                    'is_active' => true,
                ])->id,
            ]);
        },
        'The promotion does not apply to this vehicle.',
    ],
    'inactive promotion' => [
        function (array $scenario, array &$arguments): void {
            $arguments['promotion']->update(['is_active' => false]);
        },
        'The promotion is not active.',
    ],
    'before promotion validity period' => [
        function (array $scenario, array &$arguments): void {
            $arguments['registeredAt'] = $arguments['promotion']->starts_at->subSecond();
        },
        'The promotion is outside its validity period.',
    ],
    'after promotion validity period' => [
        function (array $scenario, array &$arguments): void {
            $arguments['registeredAt'] = $arguments['promotion']->ends_at->addSecond();
        },
        'The promotion is outside its validity period.',
    ],
]);

test('it rejects a promotion after its customer limit is reached', function () {
    $scenario = createRegistrationPricingScenario();
    $scenario['promotion']->update(['customer_limit' => 1]);

    Registration::factory()->create([
        'vehicle_id' => $scenario['vehicle']->id,
        'promotion_id' => $scenario['promotion']->id,
        'email' => 'first@example.com',
    ]);

    $pricing = $scenario['pricingService']->calculate(
        $scenario['vehicle'],
        $scenario['promotion'],
        2_000_000,
        'next@example.com',
    );

    expect($pricing['is_promotion_eligible'])->toBeFalse()
        ->and($pricing['ineligibility_reason'])
        ->toBe('The promotion customer limit has been reached.');
});
