<?php

declare(strict_types=1);

final class Booking
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';

    /** @var BookingItem[] */
    public array $items = [];
    public string $status = self::STATUS_PENDING;

    public function __construct(
        public int $id,
        public Customer $customer,
        public string $passType = 'day'
    ) {
    }

    public function addItem(BookingItem $item): void
    {
        $this->items[] = $item;
    }

    public function validate(): void
    {
        if (count($this->items) === 0) {
            throw new RuntimeException('Empty booking');
        }

        if (!filter_var($this->customer->email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email');
        }

        foreach ($this->items as $item) {
            if ($item->quantity <= 0) {
                throw new RuntimeException('Invalid quantity');
            }
        }
    }
}