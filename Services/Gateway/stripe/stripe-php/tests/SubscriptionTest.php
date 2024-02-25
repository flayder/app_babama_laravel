<?php

declare(strict_types=1);

namespace StripeJS;

class SubscriptionTest extends TestCase
{
    public function testCustomerCreateUpdateListCancel(): void
    {
        $planID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($planID);

        $customer = self::createTestCustomer();

        $sub = $customer->subscriptions->create(['plan' => $planID]);

        static::assertSame($sub->status, 'active');
        static::assertSame($sub->plan->id, $planID);

        $sub->quantity = 2;
        $sub->save();

        $sub = $customer->subscriptions->retrieve($sub->id);
        static::assertSame($sub->status, 'active');
        static::assertSame($sub->plan->id, $planID);
        static::assertSame($sub->quantity, 2);

        $subs = $customer->subscriptions->all(['limit' => 3]);
        static::assertSame(\get_class($subs->data[0]), 'StripeJS\Subscription');

        $sub->cancel(['at_period_end' => true]);

        $sub = $customer->subscriptions->retrieve($sub->id);
        static::assertSame($sub->status, 'active');
        // @codingStandardsIgnoreStart
        static::assertTrue($sub->cancel_at_period_end);
        // @codingStandardsIgnoreEnd
    }

    public function testCreateUpdateListCancel(): void
    {
        $planID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($planID);

        $customer = self::createTestCustomer();

        $sub = Subscription::create(['plan' => $planID, 'customer' => $customer->id]);

        static::assertSame($sub->status, 'active');
        static::assertSame($sub->plan->id, $planID);

        $sub->quantity = 2;
        $sub->save();

        $sub = Subscription::retrieve($sub->id);
        static::assertSame($sub->status, 'active');
        static::assertSame($sub->plan->id, $planID);
        static::assertSame($sub->quantity, 2);

        // Update the quantity parameter one more time
        $sub = Subscription::update($sub->id, ['quantity' => 3]);
        static::assertSame($sub->status, 'active');
        static::assertSame($sub->plan->id, $planID);
        static::assertSame($sub->quantity, 3);

        $subs = Subscription::all(['customer' => $customer->id, 'plan' => $planID, 'limit' => 3]);
        static::assertSame(\get_class($subs->data[0]), 'StripeJS\Subscription');

        $sub->cancel(['at_period_end' => true]);

        $sub = Subscription::retrieve($sub->id);
        static::assertSame($sub->status, 'active');
        static::assertTrue($sub->cancel_at_period_end);
    }

    public function testCreateUpdateListCancelWithItems(): void
    {
        $plan0ID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($plan0ID);

        $customer = self::createTestCustomer();

        $sub = Subscription::create([
          'customer' => $customer->id,
          'items' => [
            ['plan' => $plan0ID],
          ],
        ]);

        static::assertSame(\count($sub->items->data), 1);
        static::assertSame($sub->items->data[0]->plan->id, $plan0ID);

        $plan1ID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($plan1ID);

        $sub = Subscription::update($sub->id, [
          'items' => [
            ['plan' => $plan1ID],
          ],
        ]);

        static::assertSame(\count($sub->items->data), 2);
        static::assertSame($sub->items->data[0]->plan->id, $plan0ID);
        static::assertSame($sub->items->data[1]->plan->id, $plan1ID);
    }

    public function testDeleteDiscount(): void
    {
        $planID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($planID);

        $couponID = '25off-'.self::generateRandomString(20);
        self::retrieveOrCreateCoupon($couponID);

        $customer = self::createTestCustomer();

        $sub = $customer->subscriptions->create(
            [
                'plan' => $planID,
                'coupon' => $couponID,
            ]
        );

        static::assertSame($sub->status, 'active');
        static::assertSame($sub->plan->id, $planID);
        static::assertSame($sub->discount->coupon->id, $couponID);

        $sub->deleteDiscount();
        $sub = $customer->subscriptions->retrieve($sub->id);
        static::assertNull($sub->discount);
    }
}
