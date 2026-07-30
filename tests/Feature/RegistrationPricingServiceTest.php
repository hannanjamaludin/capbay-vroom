<?php

use App\Models\Promotion;
use App\Models\Registration;
use App\Models\Vehicle;
use App\Services\RegistrationPricingService;
use Database\Seeders\RegistrationSeeder;
use Illuminate\Support\Facades\Artisan;

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

test('it applies an eligible promotion using integer sen values', function () {
    $scenario = createRegistrationPricingScenario();
    $pricing = $scenario['pricingService']->calculate(
        $scenario['vehicle'],
        $scenario['promotion'],
        2_000_000,
        'customer.a@example.com',
    );

    expect($pricing['is_promotion_eligible'])->toBeTrue()
        ->and($pricing['promotion_id'])->toBe($scenario['promotion']->id)
        ->and($pricing['vehicle_price_sen'])->toBe(20_000_000)
        ->and($pricing['applied_discount_sen'])->toBe(3_000_000)
        ->and($pricing['final_price_sen'])->toBe(17_000_000)
        ->and($pricing['loan_amount_sen'])->toBe(15_000_000);
});

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

test('it prevents the same customer from using a promotion twice', function () {
    $scenario = createRegistrationPricingScenario();

    Registration::factory()->create([
        'vehicle_id' => $scenario['vehicle']->id,
        'promotion_id' => $scenario['promotion']->id,
        'email' => 'customer.a@example.com',
    ]);

    $pricing = $scenario['pricingService']->calculate(
        $scenario['vehicle'],
        $scenario['promotion'],
        2_000_000,
        'CUSTOMER.A@example.com',
    );

    expect($pricing['is_promotion_eligible'])->toBeFalse()
        ->and($pricing['promotion_id'])->toBeNull()
        ->and($pricing['final_price_sen'])->toBe(20_000_000);
});

test('the registration seeder creates the three pricing scenarios', function () {
    $this->seed();
    $this->seed(RegistrationSeeder::class);

    $customerA = Registration::query()->where('email', 'customer.a@example.com')->firstOrFail();
    $customerB = Registration::query()->where('email', 'customer.b@example.com')->firstOrFail();
    $customerC = Registration::query()->where('email', 'customer.c@example.com')->firstOrFail();

    expect(Registration::query()->whereIn('email', [
        'customer.a@example.com',
        'customer.b@example.com',
        'customer.c@example.com',
    ])->count())->toBe(3)
        ->and($customerA->promotion_id)->not->toBeNull()
        ->and($customerA->final_price_sen)->toBe(17_000_000)
        ->and($customerB->promotion_id)->toBeNull()
        ->and($customerB->final_price_sen)->toBe(20_000_000)
        ->and($customerC->promotion_id)->not->toBeNull()
        ->and($customerC->final_price_sen)->toBe(17_000_000);
});
