<?php

declare(strict_types=1);

namespace OrderStatusNotification;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OrderStatusNotification\Client\NotificationClientInterface;
use OrderStatusNotification\Client\SoapNotificationClient;
use OrderStatusNotification\Client\RestNotificationClient;
use OrderStatusNotification\Exception\NotificationBuilderException;
use OrderStatusNotification\Mapper\ArrayStatusMapper;
use OrderStatusNotification\Service\Notifier;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class NotificationBuilder
{
    private ?NotificationClientInterface $client = null;
    private ?ArrayStatusMapper $mapper = null;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ResponseFactoryInterface $responseFactory
    ) {
    }

    public static function create(): self
    {
        $client = new Client();
        $factory = new HttpFactory();

        return new self($client, $factory, $factory, $factory);
    }

    public function rest(string $url): self
    {
        try {
            $this->client = new RestNotificationClient(
                $url,
                $this->httpClient,
                $this->requestFactory,
                $this->streamFactory,
                $this->responseFactory
            );
        } catch (\Throwable $exception) {
            throw new NotificationBuilderException(
                'Failed to create REST client: ' . $exception->getMessage(),
                previous: $exception
            );
        }

        return $this;
    }

    public function soap(string $wsdl, string $method): self
    {
        try {
            $this->client = new SoapNotificationClient(
                $wsdl,
                $method,
                $this->responseFactory,
                $this->streamFactory
            );
        } catch (\Throwable $exception) {
            throw new NotificationBuilderException(
                'Failed to create SOAP client: ' . $exception->getMessage(),
                previous: $exception
            );
        }

        return $this;
    }

    public function mapStatus(array $statusMap): self
    {
        $this->mapper = new ArrayStatusMapper($statusMap);
        return $this;
    }

    public function send(Order $order): bool
    {
        if (null === $this->client || null === $this->mapper) {
            throw new NotificationBuilderException('Transport and status must be configured before sending.');
        }

        return (new Notifier($this->client, $this->mapper))->notify($order);
    }
}
