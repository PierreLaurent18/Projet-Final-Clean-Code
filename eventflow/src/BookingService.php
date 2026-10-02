<?php

declare(strict_types=1);

final class BookingService
{
    private PricingStrategyInterface $pricingStrategy;
    private PaymentGatewayInterface $paymentGateway;
    private BookingRepositoryInterface $bookingRepository;
    private MailerInterface $mailer;

    public function __construct(
        ?PricingStrategyInterface $pricingStrategy = null,
        ?PaymentGatewayInterface $paymentGateway = null,
        ?BookingRepositoryInterface $bookingRepository = null,
        ?MailerInterface $mailer = null
    ) {
        $this->pricingStrategy = $pricingStrategy ?? new FestivalPricingStrategy();
        $this->paymentGateway = $paymentGateway ?? new StripePaymentAdapter();
        $this->bookingRepository = $bookingRepository ?? new SqlBookingRepository();
        $this->mailer = $mailer ?? new EmailService();
    }
    public function confirm(Booking $booking): float
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

        $this->bookingRepository->save($booking, $total);

        $this->mailer->sendConfirmation($booking->customer->email, $booking->id);

        return $total;
    }
}
