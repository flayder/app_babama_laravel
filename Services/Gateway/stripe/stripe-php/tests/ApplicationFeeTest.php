<?php

declare(strict_types=1);

namespace StripeJS;

class ApplicationFeeTest extends TestCase
{
    public function testUrls(): void
    {
        $applicationFee = new ApplicationFee('abcd/efgh');
        static::assertSame(
            $applicationFee->instanceUrl(),
            '/v1/application_fees/abcd%2Fefgh'
        );
    }

    public function testList(): void
    {
        self::authorizeFromEnv();
        $d = ApplicationFee::all();
        static::assertSame($d->url, '/v1/application_fees');
    }
}
