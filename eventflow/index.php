<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$customer = new Customer(
    id: 42,
    email: 'lea@example.com',
    phone: '0612345678',
    type: 'vip'
);

$dayTicket = new Ticket(
    code: 'DAY-1',
    label: 'Pass Jour 1',
    price: 60.0
);

$booking = new Booking(
    id: 1001,
    customer: $customer,
    passType: '3days'
);

$booking->addItem(new BookingItem($dayTicket, 2));

$service = new BookingService(paymentGateway: new PayFastPaymentAdapter());
$total = $service->confirm($booking, 'payfast');

echo 'TOTAL FINAL: ' . number_format($total, 1, '.', '') . PHP_EOL;
