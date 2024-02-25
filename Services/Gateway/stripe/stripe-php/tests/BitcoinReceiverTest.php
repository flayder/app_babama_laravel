<?php

declare(strict_types=1);

namespace StripeJS;

class BitcoinReceiverTest extends TestCase
{
    public function testUrls(): void
    {
        $classUrl = BitcoinReceiver::classUrl('StripeJS_BitcoinReceiver');
        static::assertSame($classUrl, '/v1/bitcoin/receivers');
        $receiver = new BitcoinReceiver('abcd/efgh');
        $instanceUrl = $receiver->instanceUrl();
        static::assertSame($instanceUrl, '/v1/bitcoin/receivers/abcd%2Fefgh');
    }

    public function testCreate(): void
    {
        self::authorizeFromEnv();

        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        static::assertSame(100, $receiver->amount);
        static::assertNotNull($receiver->id);
    }

    public function testRetrieve(): void
    {
        self::authorizeFromEnv();

        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        $r = BitcoinReceiver::retrieve($receiver->id);
        static::assertSame($receiver->id, $r->id);

        static::assertInstanceOf('StripeJS\\BitcoinTransaction', $r->transactions->data[0]);
    }

    public function testList(): void
    {
        self::authorizeFromEnv();

        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        $receivers = BitcoinReceiver::all();
        static::assertGreaterThan(0, \count($receivers->data));
    }

    public function testListTransactions(): void
    {
        self::authorizeFromEnv();

        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');
        static::assertSame(0, \count($receiver->transactions->data));

        $transactions = $receiver->transactions->all(['limit' => 1]);
        static::assertSame(1, \count($transactions->data));
    }

    public function testDeleteWithCustomer(): void
    {
        self::authorizeFromEnv();
        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');
        $customer = Customer::create(['source' => $receiver->id]);
        $charge = Charge::create([
            'customer' => $customer->id,
            'amount' => $receiver->amount,
            'currency' => $receiver->currency,
        ]);
        $receiver = BitcoinReceiver::retrieve($receiver->id);
        $response = $receiver->delete();
        static::assertTrue($response->deleted);
    }

    public function testUpdateWithCustomer(): void
    {
        self::authorizeFromEnv();
        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');
        $customer = Customer::create(['source' => $receiver->id]);
        $receiver = BitcoinReceiver::retrieve($receiver->id);

        $receiver->description = 'a new description';
        $receiver->save();

        $base = Customer::classUrl();
        $parentExtn = $receiver['customer'];
        $extn = $receiver['id'];
        static::assertEquals("$base/$parentExtn/sources/$extn", $receiver->instanceUrl());

        $updatedReceiver = BitcoinReceiver::retrieve($receiver->id);
        static::assertEquals($receiver['description'], $updatedReceiver['description']);
    }

    public function testUpdateWithoutCustomer(): void
    {
        self::authorizeFromEnv();
        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        $receiver->description = 'a new description';
        $receiver->save();

        static::assertEquals(BitcoinReceiver::classUrl().'/'.$receiver['id'], $receiver->instanceUrl());

        $updatedReceiver = BitcoinReceiver::retrieve($receiver->id);
        static::assertEquals($receiver['description'], $updatedReceiver['description']);
    }

    public function testRefund(): void
    {
        self::authorizeFromEnv();
        $receiver = $this->createTestBitcoinReceiver('do+fill_now@stripe.com');

        $receiver = BitcoinReceiver::retrieve($receiver->id);
        static::assertNull($receiver->refund_address);

        $refundAddress = 'REFUNDHERE';
        $receiver->refund(['refund_address' => $refundAddress]);

        static::assertSame($refundAddress, $receiver->refund_address);
    }
}
