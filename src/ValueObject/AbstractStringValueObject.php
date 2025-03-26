<?php

declare(strict_types=1);

namespace OrderStatusNotification\ValueObject;

abstract readonly class AbstractStringValueObject
{
    protected string $value;

    public function __construct(string $value)
    {
        $trimmed = static::sanitize($value);

        if ($trimmed === '') {
            throw new \InvalidArgumentException(sprintf('%s cannot be empty.', static::class));
        }

        $this->value = $trimmed;
    }

    public static function fromString(string $value): static
    {
        return new static($value);
    }

    protected static function sanitize(string $value): string
    {
        return preg_replace('/\\s+/', '', trim($value));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
