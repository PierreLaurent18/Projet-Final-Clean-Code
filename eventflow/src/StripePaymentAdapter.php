<?php

declare(strict_types=1);

final class StripePaymentAdapter implements PaymentGatewayInterface
{
    private StripeClient $client;

    public function __construct(?StripeClient $client = null)
    {
        $this->client = $client ?? new StripeClient();
    }

    public function charge(float $amount): string
    {
        return $this->client->charge($amount);
    }
}