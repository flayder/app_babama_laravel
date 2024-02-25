<?php

declare(strict_types=1);

namespace StripeJS;

class StripeObjectTest extends TestCase
{
    public function testArrayAccessorsSemantics(): void
    {
        $s = new StripeJSObject();
        $s['foo'] = 'a';
        static::assertSame($s['foo'], 'a');
        static::assertTrue(isset($s['foo']));
        unset($s['foo']);
        static::assertFalse(isset($s['foo']));
    }

    public function testNormalAccessorsSemantics(): void
    {
        $s = new StripeJSObject();
        $s->foo = 'a';
        static::assertSame($s->foo, 'a');
        static::assertTrue(isset($s->foo));
        unset($s->foo);
        static::assertFalse(isset($s->foo));
    }

    public function testArrayAccessorsMatchNormalAccessors(): void
    {
        $s = new StripeJSObject();
        $s->foo = 'a';
        static::assertSame($s['foo'], 'a');

        $s['bar'] = 'b';
        static::assertSame($s->bar, 'b');
    }

    public function testKeys(): void
    {
        $s = new StripeJSObject();
        $s->foo = 'a';
        static::assertSame($s->keys(), ['foo']);
    }

    public function testToArray(): void
    {
        $s = new StripeJSObject();
        $s->foo = 'a';

        $converted = $s->__toArray();

        static::assertInternalType('array', $converted);
        static::assertArrayHasKey('foo', $converted);
        static::assertEquals('a', $converted['foo']);
    }

    public function testRecursiveToArray(): void
    {
        $s = new StripeJSObject();
        $z = new StripeJSObject();

        $s->child = $z;
        $z->foo = 'a';

        $converted = $s->__toArray(true);

        static::assertInternalType('array', $converted);
        static::assertArrayHasKey('child', $converted);
        static::assertInternalType('array', $converted['child']);
        static::assertArrayHasKey('foo', $converted['child']);
        static::assertEquals('a', $converted['child']['foo']);
    }

    public function testNonexistentProperty(): void
    {
        $s = new StripeJSObject();
        static::assertNull($s->nonexistent);
    }

    public function testPropertyDoesNotExists(): void
    {
        $s = new StripeJSObject();
        static::assertNull($s['nonexistent']);
    }

    public function testJsonEncode(): void
    {
        // We can only JSON encode our objects in PHP 5.4+. 5.3 must use ->__toJSON()
        if (version_compare(\PHP_VERSION, '5.4.0', '<')) {
            return;
        }

        $s = new StripeJSObject();
        $s->foo = 'a';

        static::assertEquals('{"foo":"a"}', json_encode($s->__toArray()));
    }

    public function testReplaceNewNestedUpdatable(): void
    {
        StripeJSObject::init(); // Populate the $nestedUpdatableAttributes Set
        $s = new StripeJSObject();

        $s->metadata = ['bar'];
        static::assertSame($s->metadata, ['bar']);
        $s->metadata = ['baz', 'qux'];
        static::assertSame($s->metadata, ['baz', 'qux']);
    }
}
