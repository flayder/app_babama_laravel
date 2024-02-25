<?php

declare(strict_types=1);

namespace StripeJS;

class PlanTest extends TestCase
{
    public function testDeletion(): void
    {
        self::authorizeFromEnv();
        $p = Plan::create([
            'amount' => 2000,
            'interval' => 'month',
            'currency' => 'usd',
            'name' => 'Plan',
            'id' => 'gold-'.self::generateRandomString(20),
        ]);
        $p->delete();
        static::assertTrue($p->deleted);
    }

    public function testFalseyId(): void
    {
        try {
            $retrievedPlan = Plan::retrieve('0');
        } catch (Error\InvalidRequest $e) {
            // Can either succeed or 404, all other errors are bad
            if (404 !== $e->httpStatus) {
                static::fail();
            }
        }
    }

    public function testSave(): void
    {
        self::authorizeFromEnv();
        $planID = 'gold-'.self::generateRandomString(20);
        $p = Plan::create([
            'amount' => 2000,
            'interval' => 'month',
            'currency' => 'usd',
            'name' => 'Plan',
            'id' => $planID,
        ]);
        $p->name = 'A new plan name';
        $p->save();
        static::assertSame($p->name, 'A new plan name');

        $StripeJSPlan = Plan::retrieve($planID);
        static::assertSame($p->name, $StripeJSPlan->name);
    }
}
