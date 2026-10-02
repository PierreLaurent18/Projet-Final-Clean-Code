<?php

declare(strict_types=1);

final class SendSmsNotificationListener implements BookingConfirmedListenerInterface
{
    public function __construct(private SmsClient $smsClient) {}

    public function handle(BookingConfirmedEvent $event): void
    {
        $phone = $event->booking->customer->phone;

        // Règle métier : envoi uniquement si le numéro est renseigné
        if ($phone !== null && trim($phone) !== '') {
            $this->smsClient->send($phone, "Réservation #{$event->booking->id} confirmée !");
        }
    }
}