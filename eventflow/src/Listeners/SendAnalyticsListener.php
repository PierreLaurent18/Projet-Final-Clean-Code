<?php

declare(strict_types=1);

final class SendAnalyticsListener implements BookingConfirmedListenerInterface
{
    public function __construct(private AnalyticsClient $analyticsClient) {}

    public function handle(BookingConfirmedEvent $event): void
    {
        $this->analyticsClient->trackBooking($event->booking, $event->total);
    }
}