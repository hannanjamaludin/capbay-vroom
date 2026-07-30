<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vehicle = Vehicle::query()->inRandomOrder()->first()
            ?? Vehicle::query()->create([
                'name' => fake()->words(2, true),
                'price_sen' => fake()->numberBetween(5_000_000, 30_000_000),
                'is_active' => true,
            ]);

        $downPaymentSen = fake()->numberBetween(
            intdiv($vehicle->price_sen, 10),
            intdiv($vehicle->price_sen, 2),
        );

        return [
            'vehicle_id' => $vehicle->id,
            'promotion_id' => null,
            'customer_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('01#-#######'),
            'status' => 'registered',
            'registered_at' => now(),
            'test_drive_scheduled_at' => null,
            'test_drive_completed_at' => null,
            'loan_approved_at' => null,
            'down_payment_sen' => $downPaymentSen,
            'vehicle_price_sen' => $vehicle->price_sen,
            'applied_discount_sen' => 0,
            'final_price_sen' => $vehicle->price_sen,
            'loan_amount_sen' => $vehicle->price_sen - $downPaymentSen,
            'purchased_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function testDriveScheduled(): static
    {
        return $this->state(fn(): array => [
            'status' => 'test_drive_scheduled',
            'test_drive_scheduled_at' => now(),
        ]);
    }

    public function testDriveCompleted(): static
    {
        return $this->state(fn(): array => [
            'status' => 'test_drive_completed',
            'test_drive_scheduled_at' => now()->subDay(),
            'test_drive_completed_at' => now(),
        ]);
    }

    public function purchased(): static
    {
        return $this->state(fn(): array => [
            'status' => 'purchased',
            'test_drive_scheduled_at' => now()->subDays(3),
            'test_drive_completed_at' => now()->subDays(2),
            'loan_approved_at' => now()->subDay(),
            'purchased_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(): array => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
