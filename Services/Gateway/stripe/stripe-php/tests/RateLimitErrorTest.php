<?php

declare(strict_types=1);

namespace StripeJS;

class RateLimitErrorTest extends TestCase
{
    private function rateLimitErrorResponse()
    {
        return [
            'error' => [],
        ];
    }

    /** @expectedException StripeJS\Error\RateLimit */
    public function testRateLimit(): void
    {
        $this->mockRequest('GET', '/v1/accounts/acct_DEF', [], $this->rateLimitErrorResponse(), 429);
        Account::retrieve('acct_DEF');
    }
}
