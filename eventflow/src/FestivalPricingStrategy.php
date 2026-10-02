<?php

declare(strict_types=1);
final class FestivalPricingStrategy implements PricingStrategyInterface 
{
    public function calculateTotal(Booking $booking): float
    {
        $totalInitial = 0.0;
        foreach ($booking->items as $item) {
            $totalInitial += $item->ticket->price * $item->quantity;
        }
        $total = $totalInitial;
        
        $discountrate = $this->getVipDiscountRate($booking->customer->type, $totalInitial);
        $total = $totalInitial * (1 - $discountrate);

        if ($booking->passType === '3days') {
            $total -= 20.0;
        }

        return max(0.0, $total);
    }
        private function getVipDiscountRate(string $customerType, float $totalInitial): float
        {
            if ($customerType !== 'vip') {
                return 0.0;
            }
            if ($totalInitial <100.0) { //5% en dessous de 100 euros
                return 0.05;
            }
            if ($totalInitial <300.0) { // 10% entre 100 et 299.9euros
                return 0.10;
            }

            return 0.15; //15% au dessus de 300 euros
            
        }
    
}