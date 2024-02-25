<?php

declare(strict_types=1);

namespace StripeJS;

class AuthenticationErrorTest extends TestCase
{
    public function testInvalidCredentials(): void
    {
        StripeJS::setApiKey('invalid');
        try {
            Customer::create();
        } catch (Error\Authentication $e) {
            static::assertSame(401, $e->getHttpStatus());
        }
    }
}
