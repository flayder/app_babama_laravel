<?php

declare(strict_types=1);

namespace StripeJS;

class ApplicationFeeRefundTest extends TestCase
{
    public function testUrls(): void
    {
        $refund = new ApplicationFeeRefund();
        $refund->id = 'refund_id';
        $refund->fee = 'fee_id';

        static::assertSame(
            $refund->instanceUrl(),
            '/v1/application_fees/fee_id/refunds/refund_id'
        );
    }
}
