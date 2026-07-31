<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\Registration;
use App\Models\Vehicle;
use App\Services\RegistrationPricingService;
use Illuminate\Database\Seeder;

class RegistrationSeeder extends Seeder
{
    public function __construct(private RegistrationPricingService $pricingService) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicle = Vehicle::query()->where('name', 'CapBay Vroom')->firstOrFail();
        $promotion = Promotion::query()->where('name', 'Vroom First 10')->firstOrFail();

        $customers = [
            [
                'customer_name' => 'Customer A',
                'email' => 'customer.a@example.com',
                'phone' => '012-1000001',
                'down_payment_sen' => 4_000_000,
            ],
            [
                'customer_name' => 'Customer B',
                'email' => 'customer.b@example.com',
                'phone' => '012-1000002',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Nur Aisyah',
                'email' => 'nur.aisyah@example.com',
                'phone' => '012-1000003',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Daniel Lee',
                'email' => 'daniel.lee@example.com',
                'phone' => '012-1000004',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Siti Hajar',
                'email' => 'siti.hajar@example.com',
                'phone' => '012-1000005',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Arjun Kumar',
                'email' => 'arjun.kumar@example.com',
                'phone' => '012-1000006',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Mei Xin',
                'email' => 'mei.xin@example.com',
                'phone' => '012-1000007',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Farid Iskandar',
                'email' => 'farid.iskandar@example.com',
                'phone' => '012-1000008',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Priya Nair',
                'email' => 'priya.nair@example.com',
                'phone' => '012-1000009',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Jason Tan',
                'email' => 'jason.tan@example.com',
                'phone' => '012-1000010',
                'down_payment_sen' => 2_000_000,
            ],
            [
                'customer_name' => 'Customer C',
                'email' => 'customer.c@example.com',
                'phone' => '012-1000011',
                'down_payment_sen' => 2_000_000,
            ],
        ];

        foreach ($customers as $customer) {
            $hasPaidDownPayment = ! in_array($customer['customer_name'], ['Customer B', 'Customer C'], true);
            $registration = Registration::query()->firstOrNew([
                'email' => $customer['email'],
            ]);
            $pricing = $this->pricingService->calculate(
                $vehicle,
                $promotion,
                $customer['down_payment_sen'],
                $customer['email'],
                hasPaidDownPayment: $hasPaidDownPayment,
                existingRegistration: $registration,
            );

            $registration->fill([
                'vehicle_id' => $vehicle->id,
                'promotion_id' => $pricing['promotion_id'],
                'customer_name' => $customer['customer_name'],
                'phone' => $customer['phone'],
                'status' => 'registered',
                'registered_at' => now(),
                'down_payment_sen' => $customer['down_payment_sen'],
                'paid_down_payment' => $hasPaidDownPayment,
                'vehicle_price_sen' => $pricing['vehicle_price_sen'],
                'applied_discount_sen' => $pricing['applied_discount_sen'],
                'final_price_sen' => $pricing['final_price_sen'],
                'loan_amount_sen' => $pricing['loan_amount_sen'],
            ])->save();
        }
    }
}
