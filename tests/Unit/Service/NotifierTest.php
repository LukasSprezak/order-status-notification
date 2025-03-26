<?php

declare(strict_types=1);

namespace OrderStatusNotification\Tests\Unit\Service;

use OrderStatusNotification\Client\NotificationClientInterface;
use OrderStatusNotification\Mapper\ArrayStatusMapper;
use OrderStatusNotification\Order;
use OrderStatusNotification\Service\Notifier;
use OrderStatusNotification\ValueObject\MappedStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(Notifier::class)]
final class NotifierTest extends TestCase
{
    #[Test]
    public function shouldMapSendNotification(): void
    {
        // Given
        $statusMap = [
            'S' => [
                'Y' => [
                    'Y2' => 'IN_TRANSIT'
                ]
            ]
        ];

        $mapper = new ArrayStatusMapper($statusMap);

        $order = (new Order())
            ->withId('ORD-999')
            ->withCurrentStatus('S')
            ->withNewStatus('Y2')
            ->withCarrier('Y')
            ->withPhoneNumbers(['+48500111222']);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);

        $client = $this->createMock(NotificationClientInterface::class);
        $client->expects($this->once())
            ->method('sendNotification')
            ->with(
                $this->identicalTo($order),
                $this->callback(fn (MappedStatus $status) => $status->getValue() === 'IN_TRANSIT')
            )
            ->willReturn($response);

        $notifier = new Notifier($client, $mapper);

        // When
        $result = $notifier->notify($order);

        // Then
        $this->assertTrue($result);
    }
}
