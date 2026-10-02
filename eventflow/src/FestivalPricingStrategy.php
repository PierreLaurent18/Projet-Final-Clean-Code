<?php

declare(strict_types=1);

final class FestivalPricingStrategy implements PricingStrategyInterface 
{
    private const PASS_3DAYS = '3days';
    private const PASS_3DAYS_DISCOUNT = 20.0;

    private const CUSTOMER_VIP = 'vip';
    private const TIER_1_THRESHOLD = 100.0;
    private const TIER_2_THRESHOLD = 300.0;

    private const DISCOUNT_TIER_LOW = 0.05;
    private const DISCOUNT_TIER_MID = 0.10;
    private const DISCOUNT_TIER_HIGH = 0.15;

    public function calculateTotal(Booking $booking): float
    {
        $totalInitial = 0.0;
        foreach ($booking->items as $item) {
            $totalInitial += $item->getSubtotal();
        }

        $discountRate = $this->getVipDiscountRate($booking->customer->type, $totalInitial);
        $total = $totalInitial * (1 - $discountRate);

        if ($booking->passType === self::PASS_3DAYS) {
            $total -= self::PASS_3DAYS_DISCOUNT;
        }

        return max(0.0, $total);
    }

    private function getVipDiscountRate(string $customerType, float $totalInitial): float
    {
        if ($customerType !== self::CUSTOMER_VIP) {
            return 0.0;
        }

        if ($totalInitial < self::TIER_1_THRESHOLD) {
            return self::DISCOUNT_TIER_LOW;
        }

        if ($totalInitial < self::TIER_2_THRESHOLD) {
            return self::DISCOUNT_TIER_MID;
        }

        return self::DISCOUNT_TIER_HIGH;
    }
}