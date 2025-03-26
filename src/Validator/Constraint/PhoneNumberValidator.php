<?php

declare(strict_types=1);

namespace OrderStatusNotification\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class PhoneNumberValidator extends ConstraintValidator
{
    private const array PATTERNS = [
        'PL' => '/^\\+48[5-9][0-9]{8}$/',
    ];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PhoneNumber) {
            throw new UnexpectedTypeException($constraint, PhoneNumber::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $pattern = self::PATTERNS[$constraint->region] ?? null;

        if (null === $pattern || !preg_match($pattern, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->setParameter('{{ region }}', $constraint->region)
                ->addViolation();
        }
    }
}
