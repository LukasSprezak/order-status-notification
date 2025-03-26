<?php

declare(strict_types=1);

namespace OrderStatusNotification\Tests\Unit\ValueObject;

use OrderStatusNotification\ValueObject\PhoneNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

#[CoversClass(PhoneNumber::class)]
final class PhoneNumberTest extends TestCase
{
    #[Test]
    #[DataProvider('validNumbers')]
    public function shouldSanitizesAndValidatesValidPhoneNumbers(string $input, string $expected): void
    {
        // Given
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        // When
        $phone = PhoneNumber::fromString($input);
        $violations = $validator->validate($phone);

        // Then
        $this->assertSame($expected, $phone->getValue());
        $this->assertCount(0, $violations, 'Phone number should be valid');
    }

    #[Test]
    #[DataProvider('invalidNumbers')]
    public function shouldFailsValidationForInvalidPhoneNumbers(string $input): void
    {
        // Given
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        // When
        $phone = PhoneNumber::fromString($input);
        $violations = $validator->validate($phone);

        // Then
        $this->assertGreaterThan(0, count($violations), 'Expected validation errors for invalid phone number');
        foreach ($violations as $violation) {
            $this->assertStringContainsString('Invalid phone number', $violation->getMessage());
        }
    }

    public static function validNumbers(): array
    {
        return [
            'plain valid' => ['+48500111222', '+48500111222'],
            'with spaces' => ['+48 500 111 222', '+48500111222'],
            'with dashes' => ['+48-500-111-222', '+48500111222'],
            'mixed spaces and dashes' => ['+48 500-111 222', '+48500111222'],
            'extra spaces' => ['  +48  500   111 222  ', '+48500111222'],
            'with country code spaced' => ['+ 48 500 111 222', '+48500111222'],
        ];
    }

    public static function invalidNumbers(): array
    {
        return [
            'no prefix' => ['500111222'],
            'too short' => ['+48500111'],
            'too long' => ['+4850011122233'],
            'wrong prefix' => ['+47500111222'],
            'starts with 0' => ['+48050111222'],
        ];
    }
}
