<?php

declare(strict_types=1);

namespace OrderStatusNotification;

use OrderStatusNotification\ValueObject\Address;
use OrderStatusNotification\ValueObject\Carrier;
use OrderStatusNotification\ValueObject\OrderId;
use OrderStatusNotification\ValueObject\OrderStatus;
use OrderStatusNotification\ValueObject\PhoneNumber;
use Symfony\Component\Validator\Validation;

final class Order
{
    private ?OrderId $id = null;
    private ?OrderStatus $currentStatus = null;
    private ?OrderStatus $newStatus = null;
    private array $phoneNumbers = [];
    private ?Address $sender = null;
    private ?Address $shipper = null;
    private ?Address $receiver = null;
    private ?Carrier $carrier = null;

    public function withId(string $id): self
    {
        $this->id = OrderId::fromString($id);
        return $this;
    }

    public function withCurrentStatus(string $status): self
    {
        $this->currentStatus = OrderStatus::fromString($status);
        return $this;
    }

    public function withNewStatus(string $status): self
    {
        $this->newStatus = OrderStatus::fromString($status);
        return $this;
    }

    public function withPhoneNumbers(array $numbers): self
    {
        $this->phoneNumbers = array_unique(array_map(static fn($number) => PhoneNumber::fromString($number), $numbers), SORT_REGULAR);
        return $this;
    }

    public function withSender(Address $address): self
    {
        $this->sender = $address;
        return $this;
    }

    public function withShipper(Address $address): self
    {
        $this->shipper = $address;
        return $this;
    }

    public function withReceiver(Address $address): self
    {
        $this->receiver = $address;
        return $this;
    }

    public function withCarrier(string $carrier): self
    {
        $this->carrier = Carrier::fromString($carrier);
        return $this;
    }

    public function validate(): array
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $violations = [];
        foreach ($this->phoneNumbers as $phone) {
            $v = $validator->validate($phone);
            foreach ($v as $violation) {
                $violations[] = $violation->getMessage();
            }
        }

        return $violations;
    }

    public function getId(): string
    {
        return $this->id->getValue();
    }

    public function getCurrentStatus(): OrderStatus
    {
        return $this->currentStatus;
    }

    public function getNewStatus(): OrderStatus
    {
        return $this->newStatus;
    }

    public function getPhoneNumbers(): array
    {
        return array_map(static fn(PhoneNumber $phone) => $phone->getValue(), $this->phoneNumbers);
    }

    public function getSender(): ?Address
    {
        return $this->sender;
    }

    public function getShipper(): ?Address
    {
        return $this->shipper;
    }

    public function getReceiver(): ?Address
    {
        return $this->receiver;
    }

    public function getCarrier(): ?Carrier
    {
        return $this->carrier;
    }
}
