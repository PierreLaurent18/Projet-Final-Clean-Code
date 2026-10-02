<?php

declare(strict_types=1);

final class BookingService
{
    private PricingStrategyInterface $pricingStrategy;
    private PaymentGatewayInterface $paymentGateway;
    private BookingRepositoryInterface $bookingRepository;
    
    /** @var BookingConfirmedListenerInterface[] */
    private array $listeners;

    public function __construct(
        ?PricingStrategyInterface $pricingStrategy = null,
        ?PaymentGatewayInterface $paymentGateway = null,
        ?BookingRepositoryInterface $bookingRepository = null,
        array $listeners = []
    ) {
        $this->pricingStrategy = $pricingStrategy ?? new FestivalPricingStrategy();
        $this->paymentGateway = $paymentGateway ?? new StripePaymentAdapter();
        $this->bookingRepository = $bookingRepository ?? new SqlBookingRepository();

        $this->listeners = count($listeners) > 0 ? $listeners : [
            new SendConfirmationEmailListener(new EmailService()),
            new AddLoyaltyPointsListener(new LoyaltyService()),
            new SendAnalyticsListener(new AnalyticsClient()),
            new SendSmsNotificationListener(new SmsClient()),
        ];
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

        $event = new BookingConfirmedEvent($booking, $total);
        foreach ($this->listeners as $listener) {
            $listener->handle($event);
        }

        return $total;
    }
}