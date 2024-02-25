<?php

declare(strict_types=1);

namespace StripeJS;

class InvalidRequestErrorTest extends TestCase
{
    public function testInvalidObject(): void
    {
        self::authorizeFromEnv();
        try {
            Customer::retrieve('invalid');
        } catch (Error\InvalidRequest $e) {
            static::assertSame(404, $e->getHttpStatus());
        }
    }

    public function testBadData(): void
    {
        self::authorizeFromEnv();
        try {
            Charge::create();
        } catch (Error\InvalidRequest $e) {
            static::assertSame(400, $e->getHttpStatus());
        }
    }
}
