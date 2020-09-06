<?php

declare(strict_types=1);

namespace brnc\Uuid\Domain\Exception;

class ExceedingParameterException extends \LogicException implements DomainUuidException
{
    private function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function createExceedingDomain(): self
    {
        return new self('Domain must not exceed 16 bits!', 1);
    }

    /** @codeCoverageIgnore */
    public static function createExceedingId(): self
    {
        return new self('Id must not exceed 64 bits!', 2);
    }

    public static function createExceedingType(): self
    {
        return new self('Type must not exceed 4 bits!', 3);
    }

    public static function createExceedingExtra(): self
    {
        return new self('Extra must not exceed 32 bits!', 4);
    }

    public static function createExceedingSubextra(): self
    {
        return new self('Subextra must not exceed 8 bits! While only the most significant 6 bits will be encoded!', 5);
    }
}
