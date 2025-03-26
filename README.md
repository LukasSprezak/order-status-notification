vendor/bin/phpunit tests

## Usage

```php
use OrderStatusNotification\NotificationBuilder;
use OrderStatusNotification\Order;
use OrderStatusNotification\ValueObject\Address;

$order = (new Order())
    ->withId('a31bc973-2508-458b-86aa-df0e75e9d7e6')
    ->withCurrentStatus('S')
    ->withNewStatus('Y2')
    ->withPhoneNumbers(['+48500111222', '+48 500111223'])
    ->withSender(new Address(name: 'Magazyn', street: 'ul. Katowicka 1', postalCode: '01-111', city: 'Katowice'))
    ->withShipper(new Address(name: 'Kurier', street: 'ul. Katowicka 1', postalCode: '01-111', city: 'Zabrze'))
    ->withReceiver(new Address(name: 'Łukasz Kowalski', street: 'ul. Katowicka 1', postalCode: '01-111', city: 'Chorzów'))
    ->withCarrier('Y');

$statusMap = [
    'S' => [
        'Y' => [
            'Y1' => 'SHIPPED',
            'Y2' => 'IN_TRANSIT',
            'Y3' => 'DELIVERED',
        ]
    ]
];

$builder = NotificationBuilder::create()
//    ->rest('http://127.0.0.1:8000/notification')
    ->soap('http://127.0.0.1:8000/wsdl/notification.wsdl','sendNotification')
    ->mapStatus($statusMap)
    ->send($order);



echo $builder ? "Notification sent\n" : "Shipping error\n";
