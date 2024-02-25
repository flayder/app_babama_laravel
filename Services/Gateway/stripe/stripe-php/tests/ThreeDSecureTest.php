<?php

declare(strict_types=1);

namespace StripeJS;

class ThreeDSecureTest extends TestCase
{
    public function testRetrieve(): void
    {
        $this->mockRequest(
            'GET',
            '/v1/3d_secure/tdsrc_test',
            [],
            [
                'id' => 'tdsrc_test',
                'object' => 'three_d_secure',
            ]
        );
        $three_d_secure = ThreeDSecure::retrieve('tdsrc_test');
        static::assertSame($three_d_secure->id, 'tdsrc_test');
    }

    public function testCreate(): void
    {
        $this->mockRequest(
            'POST',
            '/v1/3d_secure',
            [
                'card' => 'tok_test',
                'amount' => 1500,
                'currency' => 'usd',
                'return_url' => 'https://example.org/3d-secure-result',
            ],
            [
                'id' => 'tdsrc_test',
                'object' => 'three_d_secure',
            ]
        );
        $three_d_secure = ThreeDSecure::create([
                'card' => 'tok_test',
                'amount' => 1500,
                'currency' => 'usd',
                'return_url' => 'https://example.org/3d-secure-result',
        ]);
        static::assertSame($three_d_secure->id, 'tdsrc_test');
    }
}
