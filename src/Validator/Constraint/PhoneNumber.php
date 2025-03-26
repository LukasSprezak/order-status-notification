<?php

declare(strict_types=1);

namespace OrderStatusNotification\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
final class PhoneNumber extends Constraint
{
    public string $message = 'Invalid phone number "{{ string }}" for region "{{ region }}".';
    public string $region;

    public function __construct(
        string $region = 'PL',
        ?array $groups = null,
        mixed $payload = null,
        ?string $message = null
    ) {
        parent::__construct([], $groups, $payload);

        $this->region = mb_strtoupper($region);

        if (null !== $message) {
            $this->message = $message;
        }
    }

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
