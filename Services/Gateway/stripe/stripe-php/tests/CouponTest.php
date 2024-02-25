<?php

declare(strict_types=1);

namespace StripeJS;

class CouponTest extends TestCase
{
    public function testSave(): void
    {
        self::authorizeFromEnv();
        $id = 'test_coupon-'.self::generateRandomString(20);
        $c = Coupon::create(
            [
                'percent_off' => 25,
                'duration' => 'repeating',
                'duration_in_months' => 5,
                'id' => $id,
            ]
        );
        static::assertSame($id, $c->id);
        // @codingStandardsIgnoreStart
        static::assertSame(25, $c->percent_off);
        // @codingStandardsIgnoreEnd
        $c->metadata['foo'] = 'bar';
        $c->save();

        $StripeJSCoupon = Coupon::retrieve($id);
        static::assertEquals($c->metadata, $StripeJSCoupon->metadata);
    }
}
