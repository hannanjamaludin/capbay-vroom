<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicle = Vehicle::query()
            ->where('name', 'CapBay Vroom')
            ->firstOrFail();

        Promotion::create([
            'vehicle_id' => $vehicle->id,
            'name' => 'Vroom First 10',
            'discount_basis_points' => 1500,
            'minimum_down_payment_basis_point' => 1000,
            'customer_limit' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(5),
            'is_active' => true,
        ]);
    }
}
