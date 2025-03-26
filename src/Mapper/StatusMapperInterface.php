<?php

declare(strict_types=1);

namespace OrderStatusNotification\Mapper;

use OrderStatusNotification\ValueObject\Carrier;
use OrderStatusNotification\ValueObject\MappedStatus;
use OrderStatusNotification\ValueObject\OrderStatus;

interface StatusMapperInterface
{
    public function map(OrderStatus $currentStatus, OrderStatus $carrierStatus, Carrier $carrier): MappedStatus;
}
