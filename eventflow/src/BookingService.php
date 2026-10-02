<?php

declare(strict_types=1);

final class BookingService
{
    private PricingStrategyInterface $pricingStrategy;
    private PaymentGatewayInterface $paymentGateway;

    public function __construct(
        ?PricingStrategyInterface $pricingStrategy = null,
        ?PaymentGatewayInterface $paymentGateway = null
    ) {
        $this->pricingStrategy = $pricingStrategy ?? new FestivalPricingStrategy();
        $this->paymentGateway = $paymentGateway ?? new StripePaymentAdapter();
    }
    public function confirm(Booking $booking, string $paymentMethod = 'stripe'): float
    {
        if (count($booking->items) === 0) {
            throw new RuntimeException('Empty booking');
        }

        if (!filter_var($booking->customer->email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email');
        }

        foreach ($booking->items as $item) {
            if ($item->quantity <= 0) {
                throw new RuntimeException('Invalid quantity');
            }
        }

        $total = $this->pricingStrategy->calculateTotal($booking);

        if ($total > 0.0) {
            $transactionId = $this->paymentGateway->charge($total);
            echo "PAYMENT {$transactionId}" . PHP_EOL;
        } else {
            echo "PAYMENT FREE_ORDER" . PHP_EOL;
        }

        $booking->status = 'confirmed';

        echo "SQL INSERT booking={$booking->id} total={$total} status={$booking->status}" . PHP_EOL;

        $emailService = new EmailService();
        $emailService->sendConfirmation($booking->customer->email, $booking->id);

        return $total;
    }
}
