<?php

declare(strict_types=1);

namespace StripeJS;

class ProductTest extends TestCase
{
    public function testProductFalseyId(): void
    {
        try {
            StripeJS::setApiKey('sk_test_JieJALRz7rPz7boV17oMma7a');
            $retrievedProduct = Product::retrieve('0');
        } catch (Error\InvalidRequest $e) {
            // Can either succeed or 404, all other errors are bad
            if (404 !== $e->httpStatus) {
                static::fail();
            }
        }
    }

    public function testProductCreateUpdateRead(): void
    {
        StripeJS::setApiKey('sk_test_JieJALRz7rPz7boV17oMma7a');
        $ProductID = 'gold-'.self::generateRandomString(20);
        $p = Product::create([
            'name' => 'Gold Product',
            'id' => $ProductID,
            'url' => 'www.stripe.com/gold',
        ]);
        static::assertSame($p->url, 'www.stripe.com/gold');

        $p->name = 'A new Product name';
        $p->save();
        static::assertSame($p->name, 'A new Product name');
        static::assertSame($p->url, 'www.stripe.com/gold');

        $StripeJSProduct = Product::retrieve($ProductID);
        static::assertSame($p->name, $StripeJSProduct->name);
        static::assertSame($StripeJSProduct->url, 'www.stripe.com/gold');
    }

    public function testSKUCreateUpdateRead(): void
    {
        StripeJS::setApiKey('sk_test_JieJALRz7rPz7boV17oMma7a');
        $ProductID = 'silver-'.self::generateRandomString(20);
        $p = Product::create([
            'name' => 'Silver Product',
            'id' => $ProductID,
            'url' => 'www.stripe.com/silver',
        ]);

        $SkuID = 'silver-sku-'.self::generateRandomString(20);
        $sku = SKU::create([
            'price' => 500,
            'currency' => 'usd',
            'id' => $SkuID,
            'inventory' => [
                'type' => 'finite',
                'quantity' => 40,
            ],
            'product' => $ProductID,
        ]);

        $sku->price = 600;
        $sku->inventory->quantity = 50;
        $sku->save();
        static::assertSame($sku->price, 600);
        static::assertSame(50, $sku->inventory->quantity);

        $StripeJSSku = SKU::retrieve($SkuID);
        static::assertSame($sku->price, 600);
        static::assertSame('finite', $sku->inventory->type);
        static::assertSame(50, $sku->inventory->quantity);
    }

    public function testSKUProductDelete(): void
    {
        StripeJS::setApiKey('sk_test_JieJALRz7rPz7boV17oMma7a');
        $ProductID = 'silver-'.self::generateRandomString(20);
        $p = Product::create([
            'name' => 'Silver Product',
            'id' => $ProductID,
            'url' => 'stripe.com/silver',
        ]);

        $SkuID = 'silver-sku-'.self::generateRandomString(20);
        $sku = SKU::create([
            'price' => 500,
            'currency' => 'usd',
            'id' => $SkuID,
            'inventory' => [
                'type' => 'finite',
                'quantity' => 40,
            ],
            'product' => $ProductID,
        ]);

        $deletedSku = $sku->delete();
        static::assertTrue($deletedSku->deleted);

        $deletedProduct = $p->delete();
        static::assertTrue($deletedProduct->deleted);
    }

    public function testOrderCreateUpdateRetrievePayReturn(): void
    {
        StripeJS::setApiKey('sk_test_JieJALRz7rPz7boV17oMma7a');
        $ProductID = 'silver-'.self::generateRandomString(20);
        $p = Product::create([
            'name' => 'Silver Product',
            'id' => $ProductID,
            'url' => 'www.stripe.com/silver',
            'shippable' => false,
        ]);

        $SkuID = 'silver-sku-'.self::generateRandomString(20);
        $sku = SKU::create([
            'price' => 500,
            'currency' => 'usd',
            'id' => $SkuID,
            'inventory' => [
                'type' => 'finite',
                'quantity' => 40,
            ],
            'product' => $ProductID,
        ]);

        $order = Order::create([
            'items' => [
                0 => [
                    'type' => 'sku',
                    'parent' => $SkuID,
                ],
            ],
            'currency' => 'usd',
            'email' => 'foo@bar.com',
        ]);

        $order->metadata->foo = 'bar';
        $order->save();

        $StripeJSOrder = Order::retrieve($order->id);
        static::assertSame($order->metadata->foo, 'bar');

        $order->pay([
            'source' => [
                'object' => 'card',
                'number' => '4242424242424242',
                'exp_month' => '05',
                'exp_year' => '2017',
            ],
        ]);
        static::assertSame($order->status, 'paid');

        $orderReturn = $order->returnOrder();
        static::assertSame($orderReturn->order, $order->id);
    }
}
