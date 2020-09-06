<?php

declare(strict_types=1);

namespace brnc\Uuid\Domain;

use Jenssegers\Optimus\Optimus;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function testMore(): void
    {
        $optimus = new Optimus(1580030173, 59260789, 1163945558);
        $uuid    = DomainUuid::generateTimestamped(1, $optimus->encode(15));

        $new = DomainUuid::createFromString($uuid);

        self::assertNotNull($new);

        self::assertSame(15, $optimus->decode($new ? $new->getId() : 0));
    }
}
