<?php

declare(strict_types=1);

namespace StripeJS;

class BalanceTransactionTest extends TestCase
{
    public function testList(): void
    {
        self::authorizeFromEnv();
        $d = BalanceTransaction::all();
        static::assertSame($d->url, '/v1/balance/history');
    }
}
