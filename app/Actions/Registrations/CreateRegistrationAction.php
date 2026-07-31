<?php

namespace App\Actions\Registrations;

use App\Models\Registration;
use App\Models\Vehicle;
use App\Services\RegistrationPricingService;

class CreateRegistrationAction
{
    public function __construct(
        private readonly RegistrationPricingService $pricingService,
    ) {}

    /**
     * @param  array{customer_name: string, email: string, phone: string, down_payment_sen: int}  $customerData
     */
    public function handle(Vehicle $vehicle, array $customerData): Registration
    {
        $registeredAt = now();
        $pricing = $this->pricingService->calculate(
            vehicle: $vehicle,
            promotion: null,
            downPaymentSen: $customerData['down_payment_sen'],
            customerEmail: $customerData['email'],
            hasPaidDownPayment: false,
            registeredAt: $registeredAt,
        );

        return Registration::query()->create([
            ...$customerData,
            'vehicle_id' => $vehicle->id,
            'promotion_id' => $pricing['promotion_id'],
            'status' => 'registered',
            'registered_at' => $registeredAt,
            'paid_down_payment' => false,
            'vehicle_price_sen' => $pricing['vehicle_price_sen'],
            'applied_discount_sen' => $pricing['applied_discount_sen'],
            'final_price_sen' => $pricing['final_price_sen'],
            'loan_amount_sen' => $pricing['loan_amount_sen'],
        ]);
    }
}
