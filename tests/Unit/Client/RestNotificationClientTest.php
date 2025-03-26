<?php

declare(strict_types=1);

namespace OrderStatusNotification\Tests\Unit\Client;

use GuzzleHttp\Psr7\HttpFactory;
use OrderStatusNotification\Client\RestNotificationClient;
use OrderStatusNotification\Order;
use OrderStatusNotification\ValueObject\Address;
use OrderStatusNotification\ValueObject\MappedStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(RestNotificationClient::class)]
final class RestNotificationClientTest extends TestCase
{
    private const string ENDPOINT = 'https://api.example-test.com/notify';

    #[Test]
    public function shouldSendNotificationSuccessfully(): void
    {
        $order = $this->givenOrder();
        $status = new MappedStatus('DELIVERED');
        $response = $this->givenResponse(200);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('sendRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($response);

        $client = new RestNotificationClient(
            self::ENDPOINT,
            $httpClient,
            new HttpFactory(),
            new HttpFactory(),
            new HttpFactory()
        );

        $result = $client->sendNotification($order, $status);

        $this->assertSame($result->getStatusCode(), 200);
    }

    #[Test]
    public function shouldReturn503ResponseOnException(): void
    {
        $order = $this->givenOrder();
        $status = new MappedStatus('ERROR');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')->willThrowException(new \RuntimeException('Network error'));

        $client = new RestNotificationClient(
            self::ENDPOINT,
            $httpClient,
            new HttpFactory(),
            new HttpFactory(),
            new HttpFactory()
        );

        $response = $client->sendNotification($order, $status);

        $this->assertSame(503, $response->getStatusCode());
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
            ->withReceiver(new Address('Łukasz Kowalski', 'ul. Katowicka 1', '01-111', 'Chorzów'))
            ->withCarrier('Y');
    }

    private function givenResponse(int $statusCode): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        return $response;
    }
}
