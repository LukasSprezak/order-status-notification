<?php

declare(strict_types=1);

namespace OrderStatusNotification\Service;

use OrderStatusNotification\Client\NotificationClientInterface;
use OrderStatusNotification\Exception\InvalidOrderException;
use OrderStatusNotification\Mapper\StatusMapperInterface;
use OrderStatusNotification\Order;

final readonly class Notifier
{
    public function __construct(
        private NotificationClientInterface $client,
        private StatusMapperInterface $mapper
    ) {}

    public function notify(Order $order): bool
    {
        $errors = $order->validate();
        if (!empty($errors)) {
            throw new InvalidOrderException(implode(', ', $errors));
        }

        $carrier = $order->getCarrier();
        if (null === $carrier) {
            throw new InvalidOrderException('The carrier must be added.');
        }

        $mappedStatus = $this->mapper->map(
            $order->getCurrentStatus(),
            $order->getNewStatus(),
            $carrier
        );

        $response = $this->client->sendNotification($order, $mappedStatus);
        return 200 === $response->getStatusCode();
    }
}
