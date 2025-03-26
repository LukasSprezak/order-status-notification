<?php

declare(strict_types=1);

namespace OrderStatusNotification\Mapper;

use OrderStatusNotification\Exception\MappingNotFoundException;
use OrderStatusNotification\ValueObject\Carrier;
use OrderStatusNotification\ValueObject\MappedStatus;
use OrderStatusNotification\ValueObject\OrderStatus;

final readonly class ArrayStatusMapper implements StatusMapperInterface
{
    public function __construct(
        private array $statusMap
    ) {
    }

    public function map(OrderStatus $currentStatus, OrderStatus $carrierStatus, Carrier $carrier): MappedStatus
    {
        $current = $currentStatus->getValue();
        $carrierName = $carrier->getValue();
        $carrierStat = $carrierStatus->getValue();

        if (!isset($this->statusMap[$current][$carrierName][$carrierStat])) {
            throw new MappingNotFoundException(sprintf('%s|%s', $current, $carrierName), $carrierStat);
        }

        return new MappedStatus($this->statusMap[$current][$carrierName][$carrierStat]);
    }
}
