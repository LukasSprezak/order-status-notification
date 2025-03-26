<?php

declare(strict_types=1);

namespace OrderStatusNotification\Client;

use OrderStatusNotification\Order;
use OrderStatusNotification\ValueObject\MappedStatus;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class RestNotificationClient implements NotificationClientInterface
{
    public function __construct(
        private string $apiUrl,
        private HttpClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private ResponseFactoryInterface $responseFactory
    ) {
        if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException(sprintf('Invalid API URL: %s', $apiUrl));
        }
    }

    public function sendNotification(Order $order, MappedStatus $mappedStatus): ResponseInterface
    {
        try {
            $payload = $this->buildPayload($order, $mappedStatus);
            return $this->sendRequest($payload);
        } catch (\Throwable $exception) {
            return $this->responseFactory
                ->createResponse(503)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(
                    $this->streamFactory->createStream(json_encode([
                        'error' => 'Notification failed',
                        'details' => $exception->getMessage(),
                    ], JSON_THROW_ON_ERROR))
                );
        }
    }

    private function buildPayload(Order $order, MappedStatus $mappedStatus): array
    {
        return [
            'orderId' => $order->getId(),
            'status' => $mappedStatus->getValue(),
            'phoneNumbers' => $order->getPhoneNumbers(),
            'carrier' => $order->getCarrier()?->getValue() ?? '',
            'sender' => $order->getSender()?->toArray() ?? [],
            'shipper' => $order->getShipper()?->toArray() ?? [],
            'receiver' => $order->getReceiver()?->toArray() ?? [],
        ];
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    private function sendRequest(array $payload): ResponseInterface
    {
        $body = $this->streamFactory->createStream(json_encode($payload, JSON_THROW_ON_ERROR));

        $request = $this->requestFactory
            ->createRequest('POST', $this->apiUrl)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        return $this->httpClient->sendRequest($request);
    }
}
