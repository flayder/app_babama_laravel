<?php

declare(strict_types=1);

namespace StripeJS;

class ErrorTest extends TestCase
{
    public function testCreation(): void
    {
        try {
            throw new Error\Api('hello', 500, "{'foo':'bar'}", ['foo' => 'bar']);
            static::fail('Did not raise error');
        } catch (Error\Api $e) {
            static::assertSame('hello', $e->getMessage());
            static::assertSame(500, $e->getHttpStatus());
            static::assertSame("{'foo':'bar'}", $e->getHttpBody());
            static::assertSame(['foo' => 'bar'], $e->getJsonBody());
            static::assertNull($e->getHttpHeaders());
            static::assertNull($e->getRequestId());
        }
    }

    public function testResponseHeaders(): void
    {
        try {
            throw new Error\Api('hello', 500, "{'foo':'bar'}", ['foo' => 'bar'], ['Request-Id' => 'req_bar']);
            static::fail('Did not raise error');
        } catch (Error\Api $e) {
            static::assertSame(['Request-Id' => 'req_bar'], $e->getHttpHeaders());
            static::assertSame('req_bar', $e->getRequestId());
        }
    }

    public function testCode(): void
    {
        try {
            throw new Error\Card('hello', 'some_param', 'some_code', 400, "{'foo':'bar'}", ['foo' => 'bar']);
            static::fail('Did not raise error');
        } catch (Error\Card $e) {
            static::assertSame('some_param', $e->getStripeJSParam());
            static::assertSame('some_code', $e->getStripeJSCode());
        }
    }
}
