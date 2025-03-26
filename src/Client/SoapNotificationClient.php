<?php

declare(strict_types=1);

namespace OrderStatusNotification\Client;

use OrderStatusNotification\Order;
use OrderStatusNotification\ValueObject\MappedStatus;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class SoapNotificationClient implements NotificationClientInterface
{
    private \SoapClient $soapClient;

    public function __construct(
        string $wsdlUrl,
        private string $method,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        ?\SoapClient $soapClient = null
    ) {
        $this->soapClient = $soapClient ?? $this->createDefaultClient($wsdlUrl);
    }

    public function sendNotification(Order $order, MappedStatus $mappedStatus): ResponseInterface
    {
        try {
            $response = $this->callSoapMethod($this->buildPayload($order, $mappedStatus));

            return $this->responseFactory
                ->createResponse()
                ->withHeader('Content-Type', 'application/json')
                ->withBody(
                    $this->streamFactory->createStream(
                        json_encode(['result' => (bool) $response], JSON_THROW_ON_ERROR)
                    )
                );
        } catch (\SoapFault $exception) {
            return $this->responseFactory
                ->createResponse(503)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(
                    $this->streamFactory->createStream(
                        json_encode([
                            'error' => 'SOAP call failed',
                            'details' => $exception->getMessage(),
                        ], JSON_THROW_ON_ERROR)
                    )
                );
        }
    }

    private function buildPayload(Order $order, MappedStatus $mappedStatus): array
    {
        return [
            'orderId' => $order->getId(),
            'status' => $mappedStatus->getValue(),
            'phoneNumbers' => implode(',', $order->getPhoneNumbers()),
            'carrier' => $order->getCarrier()?->getValue() ?? '',
            'sender' => $order->getSender()?->toArray() ?? [],
            'shipper' => $order->getShipper()?->toArray() ?? [],
            'receiver' => $order->getReceiver()?->toArray() ?? [],
        ];
    }

    private function callSoapMethod(array $payload): mixed
    {
        return $this->soapClient->__soapCall($this->method, [$payload]);
    }

    private function createDefaultClient(string $wsdlUrl): \SoapClient
    {
        return new \SoapClient($wsdlUrl, [
            'trace' => false,
            'exceptions' => true,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        ]);
    }
}
