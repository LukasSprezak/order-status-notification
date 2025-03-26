<?php

declare(strict_types=1);

namespace OrderStatusNotification\ValueObject;

final readonly class OrderId extends AbstractStringValueObject {

    protected static function sanitize(string $value): string
    {
        return trim($value);
    }
}
