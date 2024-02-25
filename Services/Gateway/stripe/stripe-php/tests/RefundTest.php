<?php

declare(strict_types=1);

namespace StripeJS;

class RefundTest extends TestCase
{
    public function testCreate(): void
    {
        $charge = self::createTestCharge();
        $refund = Refund::create(['amount' => 100, 'charge' => $charge->id]);
        static::assertSame(100, $refund->amount);
        static::assertSame($charge->id, $refund->charge);
    }

    public function testUpdateAndRetrieve(): void
    {
        $charge = self::createTestCharge();
        $ref = Refund::create(['amount' => 100, 'charge' => $charge->id]);
        $ref->metadata['key'] = 'value';
        $ref->save();
        $ref = Refund::retrieve($ref->id);
        static::assertSame('value', $ref->metadata['key'], 'value');
    }

    public function testListForCharge(): void
    {
        $charge = self::createTestCharge();
        $refA = Refund::create(['amount' => 100, 'charge' => $charge->id]);
        $refB = Refund::create(['amount' => 50, 'charge' => $charge->id]);

        $all = Refund::all(['charge' => $charge]);
        static::assertFalse($all['has_more']);
        static::assertSame(2, \count($all->data));
        static::assertSame($refB->id, $all->data[0]->id);
        static::assertSame($refA->id, $all->data[1]->id);
    }

    public function testList(): void
    {
        $all = Refund::all();

        // Fetches all refunds on this test account.
        static::assertTrue($all['has_more']);
        static::assertSame(10, \count($all->data));
    }

    public function testCreateForBitcoin(): void
    {
        self::authorizeFromEnv();

        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        $charge = Charge::create(
            [
                'amount' => $receiver->amount,
                'currency' => $receiver->currency,
                'description' => $receiver->description,
                'source' => $receiver->id,
            ]
        );

        $ref = Refund::create(
            [
                'amount' => $receiver->amount,
                'refund_address' => 'ABCDEF',
                'charge' => $charge->id,
            ]
        );
        static::assertSame($receiver->amount, $ref->amount);
        static::assertNotNull($ref->id);
    }

    // Deprecated charge endpoints:

    public function testCreateViaCharge(): void
    {
        $charge = self::createTestCharge();
        $ref = $charge->refunds->create(['amount' => 100]);
        static::assertSame(100, $ref->amount);
        static::assertSame($charge->id, $ref->charge);
    }

    public function testUpdateAndRetrieveViaCharge(): void
    {
        $charge = self::createTestCharge();
        $ref = $charge->refunds->create(['amount' => 100]);
        $ref->metadata['key'] = 'value';
        $ref->save();
        $ref = $charge->refunds->retrieve($ref->id);
        static::assertSame('value', $ref->metadata['key'], 'value');
    }

    public function testListViaCharge(): void
    {
        $charge = self::createTestCharge();
        $refA = $charge->refunds->create(['amount' => 50]);
        $refB = $charge->refunds->create(['amount' => 50]);

        $all = $charge->refunds->all();
        static::assertFalse($all['has_more']);
        static::assertSame(2, \count($all->data));
        static::assertSame($refB->id, $all->data[0]->id);
        static::assertSame($refA->id, $all->data[1]->id);
    }

    public function testCreateForBitcoinViaCharge(): void
    {
        self::authorizeFromEnv();

        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        $charge = Charge::create(
            [
                'amount' => $receiver->amount,
                'currency' => $receiver->currency,
                'description' => $receiver->description,
                'source' => $receiver->id,
            ]
        );

        $ref = $charge->refunds->create(
            [
                'amount' => $receiver->amount,
                'refund_address' => 'ABCDEF',
            ]
        );
        static::assertSame($receiver->amount, $ref->amount);
        static::assertNotNull($ref->id);
    }
}
