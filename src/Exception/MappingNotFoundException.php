<?php

declare(strict_types=1);

namespace OrderStatusNotification\Exception;

final class MappingNotFoundException extends \RuntimeException
{
    public function __construct(string $currentStatus, string $carrierStatus)
    {
        $message = sprintf(
            'No mapping found for status combination: "%s" -> "%s"',
            $currentStatus,
            $carrierStatus
        );

        parent::__construct($message);
    }
}
