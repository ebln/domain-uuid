<?php

declare(strict_types=1);

namespace brnc\Uuid\Domain;

use brnc\Uuid\Domain\Exception\ExceedingParameterException;
use brnc\Uuid\Domain\Exception\NoDomainUuidException;
use Jenssegers\Optimus\Optimus;
use PHPUnit\Framework\TestCase;

class DomainUuidTest extends TestCase
{
    public function testFromString(): void
    {
        self::assertInstanceOf(DomainUuid::class, DomainUuid::createFromString('cd7e762f-6795-3ae5-8c60-6aded6aa50d6'));
    }

    public function testFromBogusVersionString(): void
    {
        self::assertNull(DomainUuid::createFromString('a050b517-6677-5119-9a77-2d26bbf30507'));
    }

    public function testFromBogusVariantString(): void
    {
        $this->expectException(NoDomainUuidException::class);
        DomainUuid::createFromString('00000000-0000-3000-C000-000000000046');
    }

    public function testGenerateTooLargeDomain(): void
    {
        $this->expectException(ExceedingParameterException::class);
        DomainUuid::generate(65536, 0, 0, 0, 0);
    }

    public function testGenerateTooLargeType(): void
    {
        $this->expectException(ExceedingParameterException::class);
        DomainUuid::generate(65535, 0, 16, 0, 0);
    }

    public function testGenerateTooLargeExtra(): void
    {
        $this->expectException(ExceedingParameterException::class);
        DomainUuid::generate(65535, 0, 15, 4294967296, 0);
    }

    public function testGenerateTooLargeSubExtra(): void
    {
        $this->expectException(ExceedingParameterException::class);
        DomainUuid::generate(65535, PHP_INT_MAX, 15, 4294967295, 256);
    }

    public function testObfuscatedIds(): void
    {
        $optimus = new Optimus(1580030173, 59260789, 1163945558);

        $uuid = DomainUuid::generateTimestamped(1, $optimus->encode(15));

        $new = DomainUuid::createFromString($uuid);

        self::assertNotNull($new);
        self::assertSame(15, $optimus->decode($new ? $new->getId() : 0));
    }
}
