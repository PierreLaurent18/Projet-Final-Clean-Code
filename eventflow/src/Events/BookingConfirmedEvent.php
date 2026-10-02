<?php

declare(strict_types=1);

final class BookingConfirmedEvent
{
    public function __construct(
        public readonly Booking $booking,
        public readonly float $total
    ) {}
}