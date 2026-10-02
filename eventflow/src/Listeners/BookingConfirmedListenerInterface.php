<?php

declare(strict_types=1);

interface BookingConfirmedListenerInterface
{
    public function handle(BookingConfirmedEvent $event): void;
}