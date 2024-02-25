<?php

declare(strict_types=1);

namespace StripeJS;

class CountrySpecTest extends TestCase
{
    public function testRetrieve(): void
    {
        self::authorizeFromEnv();

        $country = 'US';
        $d = CountrySpec::retrieve($country);
        static::assertSame($d->object, 'country_spec');
        static::assertSame($d->id, $country);
        static::assertGreaterThan(0, \count($d->supported_bank_account_currencies));
        static::assertGreaterThan(0, \count($d->supported_payment_currencies));
        static::assertGreaterThan(0, \count($d->supported_payment_methods));
        static::assertGreaterThan(0, \count($d->verification_fields));
    }

    public function testList(): void
    {
        self::authorizeFromEnv();

        $d = CountrySpec::all();
        static::assertSame($d->object, 'list');
        static::assertGreaterThan(0, \count($d->data));
        static::assertSame($d->data[0]->object, 'country_spec');
        static::assertInstanceOf('StripeJS\\CountrySpec', $d->data[0]);
    }
}
