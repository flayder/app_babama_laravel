<?php

declare(strict_types=1);

namespace StripeJS;

class RequestOptionsTest extends TestCase
{
    public function testStringAPIKey(): void
    {
        $opts = Util\RequestOptions::parse('foo');
        static::assertSame('foo', $opts->apiKey);
        static::assertSame([], $opts->headers);
    }

    public function testNull(): void
    {
        $opts = Util\RequestOptions::parse(null);
        static::assertNull($opts->apiKey);
        static::assertSame([], $opts->headers);
    }

    public function testEmptyArray(): void
    {
        $opts = Util\RequestOptions::parse([]);
        static::assertNull($opts->apiKey);
        static::assertSame([], $opts->headers);
    }

    public function testAPIKeyArray(): void
    {
        $opts = Util\RequestOptions::parse(
            [
                'api_key' => 'foo',
            ]
        );
        static::assertSame('foo', $opts->apiKey);
        static::assertSame([], $opts->headers);
    }

    public function testIdempotentKeyArray(): void
    {
        $opts = Util\RequestOptions::parse(
            [
                'idempotency_key' => 'foo',
            ]
        );
        static::assertNull($opts->apiKey);
        static::assertSame(['Idempotency-Key' => 'foo'], $opts->headers);
    }

    public function testKeyArray(): void
    {
        $opts = Util\RequestOptions::parse(
            [
                'idempotency_key' => 'foo',
                'api_key' => 'foo',
            ]
        );
        static::assertSame('foo', $opts->apiKey);
        static::assertSame(['Idempotency-Key' => 'foo'], $opts->headers);
    }

    /** @expectedException StripeJS\Error\Api */
    public function testWrongType(): void
    {
        $opts = Util\RequestOptions::parse(5);
    }
}
