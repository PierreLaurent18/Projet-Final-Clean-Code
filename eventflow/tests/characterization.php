<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestRunner.php';

$tests = new TestRunner();

function createBooking(
    string $customerType = 'standard',
    string $passType = 'day',
    float $price = 50.0,
    int $quantity = 1,
    ?string $phone = '0600000000',
    string $email = 'test@example.com'
): Booking {
    $customer = new Customer(1, $email, $phone, $customerType);
    $ticket = new Ticket('TEST', 'Ticket test', $price);
    $booking = new Booking(1, $customer, $passType);
    $booking->addItem(new BookingItem($ticket, $quantity));
    return $booking;
}

$service = new BookingService();

ob_start();

$standard = createBooking('standard', 'day', 50.0, 2);
$standardTotal = $service->confirm($standard, 'stripe');

$vip = createBooking('vip', 'day', 50.0, 2);
$vipTotal = $service->confirm($vip, 'stripe');

$threeDays = createBooking('standard', '3days', 60.0, 2);
$threeDaysTotal = $service->confirm($threeDays, 'stripe');

ob_end_clean();

$tests->near(100.0, $standardTotal, 'standard customer keeps initial total');
$tests->same('confirmed', $standard->status, 'booking becomes confirmed');
$tests->near(90.0, $vipTotal, 'legacy VIP rule gives 10 percent discount');
$tests->near(100.0, $threeDaysTotal, 'legacy three day pass discount is 10 euros');

try {
    $customer = new Customer(1, 'test@example.com', '0600000000', 'standard');
    $emptyBooking = new Booking(1, $customer, 'day');

    ob_start();
    $service->confirm($emptyBooking, 'stripe');
    ob_end_clean();

    $tests->same(true, false, 'Empty booking should throw exception');
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    $tests->same('Empty booking', $e->getMessage(), 'Empty booking throws expected exception');
}

try {
    $invalidEmailBooking = createBooking('standard', 'day', 50.0, 1, '0600000000', 'email-invalide');

    ob_start();
    $service->confirm($invalidEmailBooking, 'stripe');
    ob_end_clean();

    $tests->same(true, false, 'Invalid email should throw exception');
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    $tests->same('Invalid email', $e->getMessage(), 'Invalid email throws expected exception');
}

try {
    $invalidQtyBooking = createBooking('standard', 'day', 50.0, 0);

    ob_start();
    $service->confirm($invalidQtyBooking, 'stripe');
    ob_end_clean();

    $tests->same(true, false, 'Invalid quantity should throw exception');
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    $tests->same('Invalid quantity', $e->getMessage(), 'Invalid quantity throws expected exception');
}

try {
    $payfastBooking = createBooking('standard', 'day', 50.0, 1);

    ob_start();
    $totalPayfast = $service->confirm($payfastBooking, 'payfast');
    ob_end_clean();

    $tests->near(50.0, $totalPayfast, 'PayFast confirm valid booking');
    $tests->same('confirmed', $payfastBooking->status, 'PayFast booking becomes confirmed');
} catch (Throwable $e) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    $tests->same(true, false, 'PayFast should not throw exception: ' . $e->getMessage());
}

$tests->summary();