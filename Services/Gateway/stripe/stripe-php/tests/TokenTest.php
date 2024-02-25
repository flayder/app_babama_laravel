<?php

declare(strict_types=1);

namespace StripeJS;

class TokenTest extends TestCase
{
    public function testUrls(): void
    {
        static::assertSame(Token::classUrl(), '/v1/tokens');
        $token = new Token('abcd/efgh');
        static::assertSame($token->instanceUrl(), '/v1/tokens/abcd%2Fefgh');
    }
}
