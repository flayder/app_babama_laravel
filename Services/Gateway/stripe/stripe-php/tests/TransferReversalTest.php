<?php

declare(strict_types=1);

namespace StripeJS;

class TransferReversalTest extends TestCase
{
    // The resource that was traditionally called "transfer" became a "payout"
    // in API version 2017-04-06. We're testing traditional transfers here, so
    // we force the API version just prior anywhere that we need to.
    private $opts = ['StripeJS_version' => '2017-02-14'];

    public function testList(): void
    {
        $transfer = self::createTestTransfer([], $this->opts);
        $all = $transfer->reversals->all();
        static::assertFalse($all['has_more']);
        static::assertSame(0, \count($all->data));
    }
}
