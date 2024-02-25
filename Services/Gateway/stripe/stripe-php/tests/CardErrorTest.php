<?php

declare(strict_types=1);

namespace StripeJS;

class CardErrorTest extends TestCase
{
    public function testDecline(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4000000000000002',
            'exp_month' => '3',
            'exp_year' => '2020',
        ];

        $charge = [
            'amount' => 100,
            'currency' => 'usd',
            'card' => $card,
        ];

        try {
            Charge::create($charge);
        } catch (Error\Card $e) {
            static::assertSame(402, $e->getHttpStatus());
            static::assertTrue(str_starts_with($e->getRequestId(), 'req_'), $e->getRequestId());
            $actual = $e->getJsonBody();
            static::assertSame(
                ['error' => [
                    'message' => 'Your card was declined.',
                    'type' => 'card_error',
                    'code' => 'card_declined',
                    'decline_code' => 'generic_decline',
                    'charge' => $actual['error']['charge'],
                ]],
                $actual
            );
        }
    }
}
