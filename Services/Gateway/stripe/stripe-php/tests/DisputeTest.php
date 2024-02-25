<?php

declare(strict_types=1);

namespace StripeJS;

class DisputeTest extends TestCase
{
    public function testUrls(): void
    {
        static::assertSame(Dispute::classUrl(), '/v1/disputes');
        $dispute = new Dispute('dp_123');
        static::assertSame($dispute->instanceUrl(), '/v1/disputes/dp_123');
    }

    private function createDisputedCharge()
    {
        $card = [
            'number' => '4000000000000259',
            'exp_month' => 5,
            'exp_year' => date('Y') + 1,
        ];

        $c = Charge::create(
            [
                'amount' => 100,
                'currency' => 'usd',
                'card' => $card,
            ]
        );
        $c = Charge::retrieve($c->id);

        $attempts = 0;

        while (null === $c->dispute) {
            if ($attempts > 5) {
                throw new \Exception('Charge is taking too long to be disputed');
            }
            sleep(1);
            $c = Charge::retrieve($c->id);
            ++$attempts;
        }

        return $c;
    }

    public function testAll(): void
    {
        self::authorizeFromEnv();

        $sublist = Dispute::all(
            [
                'limit' => 3,
            ]
        );
        static::assertSame(3, \count($sublist->data));
    }

    public function testUpdate(): void
    {
        self::authorizeFromEnv();

        $c = $this->createDisputedCharge();

        $d = Dispute::retrieve($c->dispute);
        $d->evidence['customer_name'] = 'Bob';
        $s = $d->save();

        static::assertSame($c->dispute, $s->id);
        static::assertSame('Bob', $s->evidence['customer_name']);
    }

    public function testClose(): void
    {
        self::authorizeFromEnv();

        $c = $this->createDisputedCharge();
        $d = Dispute::retrieve($c->dispute);

        static::assertNotSame('lost', $d->status);

        $d->close();

        static::assertSame('lost', $d->status);
    }

    public function testRetrieve(): void
    {
        self::authorizeFromEnv();

        $c = $this->createDisputedCharge();

        $d = Dispute::retrieve($c->dispute);

        static::assertSame($c->dispute, $d->id);
    }
}
