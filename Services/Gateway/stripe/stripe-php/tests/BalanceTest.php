<?php

declare(strict_types=1);

namespace StripeJS;

class BalanceTest extends TestCase
{
    public function testRetrieve(): void
    {
        self::authorizeFromEnv();
        $d = Balance::retrieve();
        static::assertSame($d->object, 'balance');
        static::assertTrue(Util\Util::isList($d->available));
        static::assertTrue(Util\Util::isList($d->pending));
    }
}
