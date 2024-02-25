<?php

declare(strict_types=1);

namespace StripeJS;

class InvoiceTest extends TestCase
{
    public function testUpcoming(): void
    {
        self::authorizeFromEnv();
        $customer = self::createTestCustomer();

        InvoiceItem::create([
            'customer' => $customer->id,
            'amount' => 0,
            'currency' => 'usd',
        ]);

        $invoice = Invoice::upcoming([
            'customer' => $customer->id,
        ]);
        static::assertSame($invoice->customer, $customer->id);
        static::assertSame($invoice->attempted, false);
    }

    public function testItemsAccessWithParameter(): void
    {
        self::authorizeFromEnv();
        $customer = self::createTestCustomer();

        InvoiceItem::create([
            'customer' => $customer->id,
            'amount' => 100,
            'currency' => 'usd',
        ]);

        $invoice = Invoice::upcoming(
            [
            'customer' => $customer->id,
            ]
        );

        $lines = $invoice->lines->all(['limit' => 10]);

        static::assertSame(\count($lines->data), 1);
        static::assertSame($lines->data[0]->amount, 100);
    }

    // This is really just making sure that this operation does not trigger any
    // warnings, as it's highly nested.
    public function testAll(): void
    {
        self::authorizeFromEnv();
        $invoices = Invoice::all();
        static::assertGreaterThan(0, \count($invoices));
    }
}
