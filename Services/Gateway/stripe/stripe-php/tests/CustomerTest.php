<?php

declare(strict_types=1);

namespace StripeJS;

class CustomerTest extends TestCase
{
    public function testDeletion(): void
    {
        $customer = self::createTestCustomer();
        $customer->delete();

        static::assertTrue($customer->deleted);
        static::assertNull($customer['active_card']);
    }

    public function testSave(): void
    {
        $customer = self::createTestCustomer();

        $customer->email = 'gdb@stripe.com';
        $customer->save();
        static::assertSame($customer->email, 'gdb@stripe.com');

        $StripeJSCustomer = Customer::retrieve($customer->id);
        static::assertSame($customer->email, $StripeJSCustomer->email);

        StripeJS::setApiKey(null);
        $customer = Customer::create(null, self::API_KEY);
        $customer->email = 'gdb@stripe.com';
        $customer->save();

        self::authorizeFromEnv();
        $updatedCustomer = Customer::retrieve($customer->id);
        static::assertSame($updatedCustomer->email, 'gdb@stripe.com');
    }

    /** @expectedException StripeJS\Error\InvalidRequest */
    public function testBogusAttribute(): void
    {
        $customer = self::createTestCustomer();
        $customer->bogus = 'bogus';
        $customer->save();
    }

    /** @expectedException InvalidArgumentException */
    public function testUpdateDescriptionEmpty(): void
    {
        $customer = self::createTestCustomer();
        $customer->description = '';
    }

    public function testUpdateDescriptionNull(): void
    {
        $customer = self::createTestCustomer(['description' => 'foo bar']);
        $customer->description = null;

        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        static::assertNull($updatedCustomer->description);
    }

    public function testUpdateMetadata(): void
    {
        $customer = self::createTestCustomer();

        $customer->metadata['test'] = 'foo bar';
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        static::assertSame('foo bar', $updatedCustomer->metadata['test']);
    }

    public function testDeleteMetadata(): void
    {
        $customer = self::createTestCustomer();

        $customer->metadata = null;
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        static::assertSame(0, \count($updatedCustomer->metadata->keys()));
    }

    public function testUpdateSomeMetadata(): void
    {
        $customer = self::createTestCustomer();
        $customer->metadata['shoe size'] = '7';
        $customer->metadata['shirt size'] = 'XS';
        $customer->save();

        $customer->metadata['shoe size'] = '9';
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        static::assertSame('XS', $updatedCustomer->metadata['shirt size']);
        static::assertSame('9', $updatedCustomer->metadata['shoe size']);
    }

    public function testUpdateAllMetadata(): void
    {
        $customer = self::createTestCustomer();
        $customer->metadata['shoe size'] = '7';
        $customer->metadata['shirt size'] = 'XS';
        $customer->save();

        $customer->metadata = ['shirt size' => 'XL'];
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        static::assertSame('XL', $updatedCustomer->metadata['shirt size']);
        static::assertFalse(isset($updatedCustomer->metadata['shoe size']));
    }

    /** @expectedException StripeJS\Error\InvalidRequest */
    public function testUpdateInvalidMetadata(): void
    {
        $customer = self::createTestCustomer();
        $customer->metadata = 'something';
        $customer->save();
    }

    public function testCancelSubscription(): void
    {
        $planID = 'gold-'.self::generateRandomString(20);
        self::retrieveOrCreatePlan($planID);

        $customer = self::createTestCustomer(
            [
                'plan' => $planID,
            ]
        );

        $customer->cancelSubscription(['at_period_end' => true]);
        static::assertSame($customer->subscription->status, 'active');
        static::assertTrue($customer->subscription->cancel_at_period_end);
        $customer->cancelSubscription();
        static::assertSame($customer->subscription->status, 'canceled');
    }

    public function testCustomerAddCard(): void
    {
        $token = Token::create(
            ['card' => [
                'number' => '4242424242424242',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ]]
        );

        $customer = $this->createTestCustomer();
        $createdCard = $customer->sources->create(['card' => $token->id]);
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        $updatedCards = $updatedCustomer->sources->all();
        static::assertSame(\count($updatedCards['data']), 2);
    }

    public function testCustomerUpdateCard(): void
    {
        $customer = $this->createTestCustomer();
        $customer->save();

        $sources = $customer->sources->all();
        static::assertSame(\count($sources['data']), 1);

        $card = $sources['data'][0];
        $card->name = 'Jane Austen';
        $card->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        $updatedCards = $updatedCustomer->sources->all();
        static::assertSame($updatedCards['data'][0]->name, 'Jane Austen');
    }

    public function testCustomerDeleteCard(): void
    {
        $token = Token::create(
            ['card' => [
                'number' => '4242424242424242',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ]]
        );

        $customer = $this->createTestCustomer();
        $createdCard = $customer->sources->create(['card' => $token->id]);
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        $updatedCards = $updatedCustomer->sources->all();
        static::assertSame(\count($updatedCards['data']), 2);

        $deleteStatus = $updatedCustomer->sources->retrieve($createdCard->id)->delete();
        static::assertTrue($deleteStatus->deleted);
        $updatedCustomer->save();

        $postDeleteCustomer = Customer::retrieve($customer->id);
        $postDeleteCards = $postDeleteCustomer->sources->all();
        static::assertSame(\count($postDeleteCards['data']), 1);
    }

    public function testCustomerAddSource(): void
    {
        self::authorizeFromEnv();
        $token = Token::create(
            ['card' => [
                'number' => '4242424242424242',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ]]
        );

        $customer = $this->createTestCustomer();
        $createdSource = $customer->sources->create(['source' => $token->id]);
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        $updatedSources = $updatedCustomer->sources->all();
        static::assertSame(\count($updatedSources['data']), 2);
    }

    public function testCustomerUpdateSource(): void
    {
        $customer = $this->createTestCustomer();
        $customer->save();

        $sources = $customer->sources->all();
        static::assertSame(\count($sources['data']), 1);

        $source = $sources['data'][0];
        $source->name = 'Jane Austen';
        $source->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        $updatedSources = $updatedCustomer->sources->all();
        static::assertSame($updatedSources['data'][0]->name, 'Jane Austen');
    }

    public function testCustomerDeleteSource(): void
    {
        self::authorizeFromEnv();
        $token = Token::create(
            ['card' => [
                'number' => '4242424242424242',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ]]
        );

        $customer = $this->createTestCustomer();
        $createdSource = $customer->sources->create(['source' => $token->id]);
        $customer->save();

        $updatedCustomer = Customer::retrieve($customer->id);
        $updatedSources = $updatedCustomer->sources->all();
        static::assertSame(\count($updatedSources['data']), 2);

        $deleteStatus = $updatedCustomer->sources->retrieve($createdSource->id)->delete();
        static::assertTrue($deleteStatus->deleted);
        $updatedCustomer->save();

        $postDeleteCustomer = Customer::retrieve($customer->id);
        $postDeleteSources = $postDeleteCustomer->sources->all();
        static::assertSame(\count($postDeleteSources['data']), 1);
    }
}
