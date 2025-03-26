<?php

declare(strict_types=1);

namespace OrderStatusNotification\ValueObject;

use OrderStatusNotification\Validator\Constraint\PhoneNumber as PhoneNumberConstraint;

final class PhoneNumber
{
    #[PhoneNumberConstraint(region: 'PL')]
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        $sanitized = preg_replace('/[^+0-9]/', '', $value);

        return new self($sanitized);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
