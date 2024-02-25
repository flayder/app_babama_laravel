<?php

declare(strict_types=1);

namespace StripeJS;

class UtilTest extends TestCase
{
    public function testIsList(): void
    {
        $list = [5, 'nstaoush', []];
        static::assertTrue(Util\Util::isList($list));

        $notlist = [5, 'nstaoush', [], 'bar' => 'baz'];
        static::assertFalse(Util\Util::isList($notlist));
    }

    public function testThatPHPHasValueSemanticsForArrays(): void
    {
        $original = ['php-arrays' => 'value-semantics'];
        $derived = $original;
        $derived['php-arrays'] = 'reference-semantics';

        static::assertSame('value-semantics', $original['php-arrays']);
    }

    public function testConvertStripeJSObjectToArrayIncludesId(): void
    {
        $customer = self::createTestCustomer();
        static::assertTrue(\array_key_exists('id', $customer->__toArray(true)));
    }

    public function testUtf8(): void
    {
        // UTF-8 string
        $x = "\xc3\xa9";
        static::assertSame(Util\Util::utf8($x), $x);

        // Latin-1 string
        $x = "\xe9";
        static::assertSame(Util\Util::utf8($x), "\xc3\xa9");

        // Not a string
        $x = true;
        static::assertSame(Util\Util::utf8($x), $x);
    }
}
