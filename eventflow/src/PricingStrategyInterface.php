<?php

interface PricingStrategyInterface
{
    public function calculateTotal(Booking $booking): float;
}