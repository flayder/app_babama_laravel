<?php

declare(strict_types=1);

namespace StripeJS;

class RecipientTest extends TestCase
{
    public function testDeletion(): void
    {
        $recipient = self::createTestRecipient();
        $recipient->delete();

        static::assertTrue($recipient->deleted);
    }

    public function testSave(): void
    {
        $recipient = self::createTestRecipient();

        $recipient->email = 'gdb@stripe.com';
        $recipient->save();
        static::assertSame($recipient->email, 'gdb@stripe.com');

        $StripeJSRecipient = Recipient::retrieve($recipient->id);
        static::assertSame($recipient->email, $StripeJSRecipient->email);
    }

    /** @expectedException StripeJS\Error\InvalidRequest */
    public function testBogusAttribute(): void
    {
        $recipient = self::createTestRecipient();
        $recipient->bogus = 'bogus';
        $recipient->save();
    }

    public function testRecipientAddCard(): void
    {
        $token = Token::create(
            ['card' => [
                'number' => '4000056655665556',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ]]
        );

        $recipient = $this->createTestRecipient();
        $createdCard = $recipient->cards->create(['card' => $token->id]);
        $recipient->save();

        $updatedRecipient = Recipient::retrieve($recipient->id);
        $updatedCards = $updatedRecipient->cards->all();
        static::assertSame(\count($updatedCards['data']), 1);
    }

    public function testRecipientUpdateCard(): void
    {
        $token = Token::create(
            ['card' => [
                'number' => '4000056655665556',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ]]
        );

        $recipient = $this->createTestRecipient();
        $createdCard = $recipient->cards->create(['card' => $token->id]);
        $recipient->save();

        $createdCards = $recipient->cards->all();
        static::assertSame(\count($createdCards['data']), 1);

        $card = $createdCards['data'][0];
        $card->name = 'Jane Austen';
        $card->save();

        $updatedRecipient = Recipient::retrieve($recipient->id);
        $updatedCards = $updatedRecipient->cards->all();
        static::assertSame($updatedCards['data'][0]->name, 'Jane Austen');
    }

    public function testRecipientDeleteCard(): void
    {
        $token = Token::create(
            ['card' => [
                'number' => '4000056655665556',
                'exp_month' => 5,
                'exp_year' => date('Y') + 3,
                'cvc' => '314',
            ]]
        );

        $recipient = $this->createTestRecipient();
        $createdCard = $recipient->cards->create(['card' => $token->id]);
        $recipient->save();

        $updatedRecipient = Recipient::retrieve($recipient->id);
        $updatedCards = $updatedRecipient->cards->all();
        static::assertSame(\count($updatedCards['data']), 1);

        $deleteStatus =
        $updatedRecipient->cards->retrieve($createdCard->id)->delete();
        static::assertTrue($deleteStatus->deleted);
        $updatedRecipient->save();

        $postDeleteRecipient = Recipient::retrieve($recipient->id);
        $postDeleteCards = $postDeleteRecipient->cards->all();
        static::assertSame(\count($postDeleteCards['data']), 0);
    }
}
