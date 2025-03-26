<?php

declare(strict_types=1);

namespace OrderStatusNotification\Client;

use OrderStatusNotification\Order;
use OrderStatusNotification\ValueObject\MappedStatus;
use Psr\Http\Message\ResponseInterface;

interface NotificationClientInterface
{
    public function sendNotification(Order $order, MappedStatus $mappedStatus): ResponseInterface;
}
