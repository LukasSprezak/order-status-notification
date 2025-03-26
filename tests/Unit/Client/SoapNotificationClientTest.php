<?php

declare(strict_types=1);

namespace OrderStatusNotification\Tests\Unit\Client;

use GuzzleHttp\Psr7\HttpFactory;
use OrderStatusNotification\Client\SoapNotificationClient;
use OrderStatusNotification\Order;
use OrderStatusNotification\ValueObject\Address;
use OrderStatusNotification\ValueObject\MappedStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SoapNotificationClient::class)]
final class SoapNotificationClientTest extends TestCase
{
    private const string ENDPOINT = 'http://example-test.wsdl';

    #[Test]
    public function shouldSendNotificationSuccessfully(): void
    {
        // Given
        $order = $this->givenOrder();
        $status = new MappedStatus('DELIVERED');
        $expectedPayload = $this->expectedPayload($order, $status);

        $soapMock = $this->givenSoapClient($expectedPayload);
        $client = $this->givenSoapNotificationClient($soapMock);

        // When
        $result = $client->sendNotification($order, $status);

        // Then
        $this->assertSame(200, $result->getStatusCode());
    }

    #[Test]
    public function shouldReturn503ResponseOnSoapFault(): void
    {
        // Given
        $order = $this->givenOrder();
        $status = new MappedStatus('FAILED');

        $soapMock = $this->createMock(\SoapClient::class);
        $soapMock->method('__soapCall')->willThrowException(new \SoapFault('Sender', 'Test error'));

        $client = $this->givenSoapNotificationClient($soapMock);

        // When
        $response = $client->sendNotification($order, $status);

        // Then
        $this->assertSame(503, $response->getStatusCode());
    }

    #[Test]
    public function shouldSendNotificationWithMissingShipperAndSender(): void
    {
        // Given
        $order = (new Order())
            ->withId('ORD-456')
            ->withCurrentStatus('S')
            ->withNewStatus('Y3')
            ->withPhoneNumbers(['+48500111223'])
            ->withReceiver(new Address('Klient', 'ul. Katowicka 1', '01-111', 'Chorzów'))
            ->withCarrier('Y');

        $status = new MappedStatus('READY');
        $expectedPayload = $this->expectedPayload($order, $status);

        $soapMock = $this->givenSoapClient($expectedPayload);
        $client = $this->givenSoapNotificationClient($soapMock);

        // When
        $response = $client->sendNotification($order, $status);

        // Then
        $this->assertSame(200, $response->getStatusCode());
    }

    private function givenOrder(): Order
    {
        return (new Order())
            ->withId('ORD-123')
            ->withCurrentStatus('S')
            ->withNewStatus('Y1')
            ->withPhoneNumbers(['+48500111222'])
            ->withSender(new Address('Magazyn', 'ul. Katowicka 1', '01-111', 'Katowice'))
            ->withShipper(new Address('Kurier', 'ul. Katowicka 1', '01-111', 'Zabrze'))
            ->withReceiver(new Address('Klient', 'ul. Katowicka 1', '01-111', 'Chorzów'))
            ->withCarrier('Y');
    }

    private function expectedPayload(Order $order, MappedStatus $status): array
    {
        return [
            'orderId' => $order->getId(),
            'status' => $status->getValue(),
            'phoneNumbers' => implode(',', $order->getPhoneNumbers()),
            'carrier' => $order->getCarrier()?->getValue() ?? '',
            'sender' => $order->getSender()?->toArray() ?? [],
            'shipper' => $order->getShipper()?->toArray() ?? [],
            'receiver' => $order->getReceiver()?->toArray() ?? [],
        ];
    }

    private function givenSoapClient(array $expectedPayload, mixed $response = true): \SoapClient
    {
        $soapMock = $this->createMock(\SoapClient::class);
        $soapMock->expects($this->once())
            ->method('__soapCall')
            ->with('sendNotification', [$expectedPayload])
            ->willReturn($response);

        return $soapMock;
    }

    private function givenSoapNotificationClient(\SoapClient $soapMock): SoapNotificationClient
    {
        return new SoapNotificationClient(
            self::ENDPOINT,
            'sendNotification',
            new HttpFactory(),
            new HttpFactory(),
            $soapMock
        );
    }
}
