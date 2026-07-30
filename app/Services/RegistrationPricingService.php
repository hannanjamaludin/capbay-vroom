<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\Registration;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class RegistrationPricingService
{
    /**
     * @return array{
     *     promotion_id: int|null,
     *     is_promotion_eligible: bool,
     *     ineligibility_reason: string|null,
     *     vehicle_price_sen: int,
     *     applied_discount_sen: int,
     *     final_price_sen: int,
     *     loan_amount_sen: int
     * }
     */
    public function calculate(
        Vehicle $vehicle,
        ?Promotion $promotion,
        int $downPaymentSen,
        string $customerEmail,
        ?CarbonInterface $registeredAt = null,
        ?Registration $existingRegistration = null,
    ): array {
        $registeredAt ??= now();
        $ineligibilityReason = $this->ineligibilityReason(
            $vehicle,
            $promotion,
            $downPaymentSen,
            $customerEmail,
            $registeredAt,
            $existingRegistration,
        );
        $isPromotionEligible = $promotion !== null && $ineligibilityReason === null;
        $appliedDiscountSen = $isPromotionEligible
            ? intdiv($vehicle->price_sen * $promotion->discount_basis_points, 10_000)
            : 0;
        $finalPriceSen = $vehicle->price_sen - $appliedDiscountSen;

        return [
            'promotion_id' => $isPromotionEligible ? $promotion->id : null,
            'is_promotion_eligible' => $isPromotionEligible,
            'ineligibility_reason' => $ineligibilityReason,
            'vehicle_price_sen' => $vehicle->price_sen,
            'applied_discount_sen' => $appliedDiscountSen,
            'final_price_sen' => $finalPriceSen,
            'loan_amount_sen' => max(0, $finalPriceSen - $downPaymentSen),
        ];
    }

    private function ineligibilityReason(
        Vehicle $vehicle,
        ?Promotion $promotion,
        int $downPaymentSen,
        string $customerEmail,
        CarbonInterface $registeredAt,
        ?Registration $existingRegistration,
    ): ?string {
        if ($promotion === null) {
            return 'No promotion was selected.';
        }

        if (! $vehicle->is_active) {
            return 'The vehicle is not active.';
        }

        if ($promotion->vehicle_id !== $vehicle->id) {
            return 'The promotion does not apply to this vehicle.';
        }

        if (! $promotion->is_active) {
            return 'The promotion is not active.';
        }

        if ($registeredAt->lt($promotion->starts_at) || $registeredAt->gt($promotion->ends_at)) {
            return 'The promotion is outside its validity period.';
        }

        $minimumDownPaymentSen = intdiv(
            $vehicle->price_sen * $promotion->minimum_down_payment_basis_point,
            10_000,
        );

        if ($downPaymentSen < $minimumDownPaymentSen) {
            return 'The down payment does not meet the promotion minimum.';
        }

        $normalizedEmail = Str::lower(trim($customerEmail));

        if (Registration::query()
            ->where('promotion_id', $promotion->id)
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->when(
                $existingRegistration?->exists,
                fn($query) => $query->whereKeyNot($existingRegistration->getKey()),
            )
            ->exists()
        ) {
            return 'The customer has already used this promotion.';
        }

        if (Registration::query()->where('promotion_id', $promotion->id)->count() >= $promotion->customer_limit) {
            return 'The promotion customer limit has been reached.';
        }

        return null;
    }
}
