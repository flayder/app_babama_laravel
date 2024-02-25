<?php

declare(strict_types=1);

namespace StripeJS;

class DiscountTest extends TestCase
{
    public function testDeletion(): void
    {
        self::authorizeFromEnv();
        $id = 'test-coupon-'.self::generateRandomString(20);
        $coupon = Coupon::create(
            [
                'percent_off' => 25,
                'duration' => 'repeating',
                'duration_in_months' => 5,
                'id' => $id,
            ]
        );
        $customer = self::createTestCustomer(['coupon' => $id]);

        static::assertTrue(isset($customer->discount));
        static::assertTrue(isset($customer->discount->coupon));
        static::assertSame($id, $customer->discount->coupon->id);

        $customer->deleteDiscount();
        static::assertFalse(isset($customer->discount));

        $customer = Customer::retrieve($customer->id);
        static::assertFalse(isset($customer->discount));
    }
}
