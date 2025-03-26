<?php

declare(strict_types=1);

namespace OrderStatusNotification\Tests\Unit\Mapper;

use OrderStatusNotification\Exception\MappingNotFoundException;
use OrderStatusNotification\Mapper\ArrayStatusMapper;
use OrderStatusNotification\ValueObject\Carrier;
use OrderStatusNotification\ValueObject\OrderStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayStatusMapper::class)]
final class ArrayStatusMapperTest extends TestCase
{
    #[Test]
    public function shouldReturnMappedStatusWhenExists(): void
    {
        // Given
        $map = [
            'S' => [
                'Y' => [
                    'Y2' => 'IN_TRANSIT'
                ]
            ]
        ];
        $mapper = new ArrayStatusMapper($map);

        $currentStatus = OrderStatus::fromString('S');
        $carrierStatus = OrderStatus::fromString('Y2');
        $carrier = Carrier::fromString('Y');

        // When
        $result = $mapper->map($currentStatus, $carrierStatus, $carrier);

        // Then
        $this->assertSame('IN_TRANSIT', $result->getValue());
    }

    #[Test]
    public function shouldThrowExceptionWhenMappingNotFound(): void
    {
        // Given
        $map = [
            'S' => [
                'Y' => [
                    'Y2' => 'IN_TRANSIT'
                ]
            ]
        ];
        $mapper = new ArrayStatusMapper($map);

        $currentStatus = OrderStatus::fromString('S');
        $carrierStatus = OrderStatus::fromString('Z9');
        $carrier = Carrier::fromString('Y');

        // Then
        $this->expectException(MappingNotFoundException::class);
        $this->expectExceptionMessage('No mapping found for status combination: "S|Y" -> "Z9"');

        // When
        $mapper->map($currentStatus, $carrierStatus, $carrier);
    }

    #[Test]
    public function shouldThrowExceptionWhenCarrierNotDefined(): void
    {
        // Given
        $map = [
            'S' => [
                'Y' => [
                    'Y2' => 'IN_TRANSIT'
                ]
            ]
        ];
        $mapper = new ArrayStatusMapper($map);

        $currentStatus = OrderStatus::fromString('S');
        $carrierStatus = OrderStatus::fromString('Y2');
        $carrier = Carrier::fromString('X');

        // Then
        $this->expectException(MappingNotFoundException::class);
        $this->expectExceptionMessage('No mapping found for status combination: "S|X" -> "Y2"');

        // When
        $mapper->map($currentStatus, $carrierStatus, $carrier);
    }
}
