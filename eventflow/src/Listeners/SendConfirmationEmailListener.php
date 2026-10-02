<?php

declare(strict_types=1);

final class SendConfirmationEmailListener implements BookingConfirmedListenerInterface
{
    public function __construct(private MailerInterface $mailer) {}

    public function handle(BookingConfirmedEvent $event): void
    {
        $this->mailer->sendConfirmation(
            $event->booking->customer->email,
            $event->booking->id
        );
    }
}