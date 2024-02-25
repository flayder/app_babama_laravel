<?php

declare(strict_types=1);

namespace StripeJS;

class PayoutTest extends TestCase
{
    private $managedAccount = null;

    /**
     * Create a managed account and put enough funds in the balance
     * to be able to create a payout afterwards. Also try to re-use
     * the managed account across the tests to avoid hitting the
     * rate limit for account creation.
     */
    private function createAccountWithBalance()
    {
        if (null === $this->managedAccount) {
            self::authorizeFromEnv();
            $account = self::createTestManagedAccount();

            $charge = \StripeJS\Charge::create([
                'currency' => 'usd',
                'amount' => '10000',
                'source' => [
                    'object' => 'card',
                    'number' => '4000000000000077',
                    'exp_month' => '09',
                    'exp_year' => date('Y') + 3,
                ],
                'destination' => [
                    'account' => $account->id,
                ],
            ]);

            $this->managedAccount = $account;
        }

        return $this->managedAccount;
    }

    private function createPayoutFromManagedAccount($accountId)
    {
        $payout = Payout::create(
            [
                'amount' => 100,
                'currency' => 'usd',
            ],
            [
                'StripeJS_account' => $accountId,
            ]
        );

        return $payout;
    }

    public function testCreate(): void
    {
        $account = self::createAccountWithBalance();
        $payout = self::createPayoutFromManagedAccount($account->id);

        static::assertSame('pending', $payout->status);
    }

    public function testRetrieve(): void
    {
        $account = self::createAccountWithBalance();
        $payout = self::createPayoutFromManagedAccount($account->id);
        $reloaded = Payout::retrieve($payout->id, ['StripeJS_account' => $account->id]);
        static::assertSame($reloaded->id, $payout->id);
    }

    public function testPayoutUpdateMetadata(): void
    {
        $account = self::createAccountWithBalance();
        $payout = self::createPayoutFromManagedAccount($account->id);
        $payout->metadata['test'] = 'foo bar';
        $payout->save();

        $updatedPayout = Payout::retrieve($payout->id, ['StripeJS_account' => $account->id]);
        static::assertSame('foo bar', $updatedPayout->metadata['test']);
    }

    public function testPayoutUpdateMetadataAll(): void
    {
        $account = self::createAccountWithBalance();
        $payout = self::createPayoutFromManagedAccount($account->id);

        $payout->metadata = ['test' => 'foo bar'];
        $payout->save();

        $updatedPayout = Payout::retrieve($payout->id, ['StripeJS_account' => $account->id]);
        static::assertSame('foo bar', $updatedPayout->metadata['test']);
    }
}
