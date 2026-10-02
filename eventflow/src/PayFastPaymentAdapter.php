<?php

declare(strict_types=1);

final class PayFastPaymentAdapter implements PaymentGatewayInterface
{
    private PayFastSdk $sdk;
    public function __construct(?PayFastSdk $sdk = null)
    {
        $this->sdk = $sdk ?? new PayFastSdk();
    }

    public function charge(float $amount): string
    {
        $cents = (int) round($amount * 100);
        $reference = bin2hex(random_bytes(4));

        $result = $this->sdk->executePayment([
            'reference' => $reference,
            'amount_cents' => $cents,
            'currency' => 'EUR',
        ]);

        if (!$result['success']) {
            throw new RuntimeException('Payment failed');
        }

        return $result['transaction_id'];
    }
}