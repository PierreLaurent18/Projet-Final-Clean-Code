<?php

declare(strict_types=1);

final class AddLoyaltyPointsListener implements BookingConfirmedListenerInterface
{
    public function __construct(private LoyaltyService $loyaltyService) {}

    public function handle(BookingConfirmedEvent $event): void
    {
        $this->loyaltyService->addPoints($event->booking->customer->id, (int) $event->total);
    }
}