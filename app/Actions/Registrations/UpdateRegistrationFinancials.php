<?php

namespace App\Actions\Registrations;

use App\Models\Promotion;
use App\Models\Registration;
use App\Services\RegistrationPricingService;

class UpdateRegistrationFinancials
{
    public function __construct(
        private readonly RegistrationPricingService $pricingService,
    ) {}

    public function handle(Registration $registration, int $downPaymentSen): Registration
    {
        $registration->loadMissing(['vehicle', 'promotion']);

        $promotion = $registration->promotion ?? Promotion::query()
            ->whereBelongsTo($registration->vehicle)
            ->where('is_active', true)
            ->where('starts_at', '<=', $registration->registered_at)
            ->where('ends_at', '>=', $registration->registered_at)
            ->orderByDesc('discount_basis_points')
            ->first();

        $pricing = $this->pricingService->calculate(
            vehicle: $registration->vehicle,
            promotion: $promotion,
            downPaymentSen: $downPaymentSen,
            customerEmail: $registration->email,
            registeredAt: $registration->registered_at,
            existingRegistration: $registration,
        );

        $registration->update([
            'promotion_id' => $pricing['promotion_id'],
            'down_payment_sen' => $downPaymentSen,
            'vehicle_price_sen' => $pricing['vehicle_price_sen'],
            'applied_discount_sen' => $pricing['applied_discount_sen'],
            'final_price_sen' => $pricing['final_price_sen'],
            'loan_amount_sen' => $pricing['loan_amount_sen'],
        ]);

        return $registration->refresh()->load(['vehicle', 'promotion']);
    }
}
