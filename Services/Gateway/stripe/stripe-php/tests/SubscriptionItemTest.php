<?php

declare(strict_types=1);

namespace StripeJS;

class SubscriptionItemTest extends TestCase
{
    public function testCreateUpdateRetrieveListCancel(): void
    {
        $plan0ID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($plan0ID);

        $customer = self::createTestCustomer();
        $sub = Subscription::create(['plan' => $plan0ID, 'customer' => $customer->id]);

        $plan1ID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($plan1ID);

        $subItem = SubscriptionItem::create(['plan' => $plan1ID, 'subscription' => $sub->id]);
        static::assertSame($subItem->plan->id, $plan1ID);

        $subItem->quantity = 2;
        $subItem->save();

        $subItem = SubscriptionItem::retrieve($subItem->id);
        static::assertSame($subItem->quantity, 2);

        // Update the quantity parameter one more time
        $subItem = SubscriptionItem::update($subItem->id, ['quantity' => 3]);
        static::assertSame($subItem->quantity, 3);

        $subItems = SubscriptionItem::all(['subscription' => $sub->id, 'limit' => 3]);
        static::assertSame(\get_class($subItems->data[0]), 'StripeJS\SubscriptionItem');
        static::assertSame(2, \count($subItems->data));

        $subItem->delete();
        static::assertTrue($subItem->deleted);
    }
}
