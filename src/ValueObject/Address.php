<?php

declare(strict_types=1);

namespace OrderStatusNotification\ValueObject;

final readonly class Address implements \JsonSerializable
{
    public function __construct(
        public string $name,
        public string $street,
        public string $postalCode,
        public string $city,
        public string $country = 'PL'
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'street' => $this->street,
            'postalCode' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
