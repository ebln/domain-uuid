<?php

declare(strict_types=1);

namespace brnc\Uuid\Domain\Exception;

class NoDomainUuidException extends \UnexpectedValueException implements DomainUuidException
{
    private function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function createInvalid(): self
    {
        return new self('Provided string is not a valid DomainUuid!');
    }
}
