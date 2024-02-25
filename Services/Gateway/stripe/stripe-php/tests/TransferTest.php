<?php

declare(strict_types=1);

namespace StripeJS;

class TransferTest extends TestCase
{
    // The resource that was traditionally called "transfer" became a "payout"
    // in API version 2017-04-06. We're testing traditional transfers here, so
    // we force the API version just prior anywhere that we need to.
    private $opts = ['StripeJS_version' => '2017-02-14'];

    public function testCreate(): void
    {
        $transfer = self::createTestTransfer([], $this->opts);
        static::assertSame('transfer', $transfer->object);
    }

    public function testRetrieve(): void
    {
        $transfer = self::createTestTransfer([], $this->opts);
        $reloaded = Transfer::retrieve($transfer->id, $this->opts);
        static::assertSame($reloaded->id, $transfer->id);
    }

    public function testTransferUpdateMetadata(): void
    {
        $transfer = self::createTestTransfer([], $this->opts);

        $transfer->metadata['test'] = 'foo bar';
        $transfer->save();

        $updatedTransfer = Transfer::retrieve($transfer->id, $this->opts);
        static::assertSame('foo bar', $updatedTransfer->metadata['test']);
    }

    public function testTransferUpdateMetadataAll(): void
    {
        $transfer = self::createTestTransfer([], $this->opts);

        $transfer->metadata = ['test' => 'foo bar'];
        $transfer->save();

        $updatedTransfer = Transfer::retrieve($transfer->id, $this->opts);
        static::assertSame('foo bar', $updatedTransfer->metadata['test']);
    }
}
