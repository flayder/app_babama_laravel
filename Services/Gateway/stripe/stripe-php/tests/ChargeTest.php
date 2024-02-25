<?php

declare(strict_types=1);

namespace StripeJS;

class ChargeTest extends TestCase
{
    public function testUrls(): void
    {
        static::assertSame(Charge::classUrl(), '/v1/charges');
        $charge = new Charge('abcd/efgh');
        static::assertSame($charge->instanceUrl(), '/v1/charges/abcd%2Fefgh');
    }

    public function testCreate(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $c = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ]
        );
        static::assertTrue($c->paid);
        static::assertFalse($c->refunded);
    }

    public function testIdempotentCreate(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $c = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ],
            [
                'idempotency_key' => self::generateRandomString(),
            ]
        );

        static::assertTrue($c->paid);
        static::assertSame(200, $c->getLastResponse()->code);
    }

    public function testRetrieve(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $c = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ]
        );
        $d = Charge::retrieve($c->id);
        static::assertSame(200, $d->getLastResponse()->code);
        static::assertSame($d->id, $c->id);
    }

    public function testUpdateMetadata(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $charge = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ]
        );

        $charge->metadata['test'] = 'foo bar';
        $charge->save();

        $updatedCharge = Charge::retrieve($charge->id);
        static::assertSame('foo bar', $updatedCharge->metadata['test']);
    }

    public function testUpdateMetadataAll(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $charge = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ]
        );

        $charge->metadata = ['test' => 'foo bar'];
        $charge->save();
        static::assertSame(200, $charge->getLastResponse()->code);

        $updatedCharge = Charge::retrieve($charge->id);
        static::assertSame('foo bar', $updatedCharge->metadata['test']);
    }

    public function testMarkAsFraudulent(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $charge = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ]
        );

        $charge->refunds->create();
        $charge->markAsFraudulent();

        $updatedCharge = Charge::retrieve($charge->id);
        static::assertSame(
            'fraudulent',
            $updatedCharge['fraud_details']['user_report']
        );
    }

    public function testCreateWithBitcoinReceiverSource(): void
    {
        self::authorizeFromEnv();

        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        $charge = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'source' => $receiver->id,
            ]
        );

        static::assertSame($receiver->id, $charge->source->id);
        static::assertSame('bitcoin_receiver', $charge->source->object);
        static::assertSame('succeeded', $charge->status);
        static::assertInstanceOf('StripeJS\\BitcoinReceiver', $charge->source);
    }

    public function markAsSafe(): void
    {
        self::authorizeFromEnv();

        $card = [
            'number' => '4242424242424242',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $charge = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ]
        );

        $charge->markAsSafe();

        $updatedCharge = Charge::retrieve($charge->id);
        static::assertSame('safe', $updatedCharge['fraud_details']['user_report']);
    }
}
