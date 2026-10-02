<?php

declare(strict_types=1);

interface MailerInterface
{
    public function sendConfirmation(string $email, int $bookingId): void;
}