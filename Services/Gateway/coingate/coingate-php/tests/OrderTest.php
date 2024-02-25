<?php

declare(strict_types=1);

namespace CoinGate;

class OrderTest extends TestCase
{
    public function testFindOrderNotFound(): void
    {
        static::assertFalse(Merchant\Order::find(0, [], self::getGoodAuthentication()));

        try {
            static::assertFalse(Merchant\Order::findOrFail(0, [], self::getGoodAuthentication()));
        } catch (\Exception $e) {
            static::assertRegExp('/OrderNotFound/', $e->getMessage());
        }
    }

    public function testFindOrderFound(): void
    {
        $order = Merchant\Order::create(self::getGoodPostParams(), [], self::getGoodAuthentication());
        static::assertNotFalse(Merchant\Order::find($order->id, [], self::getGoodAuthentication()));
    }

    public function testCreateOrderIsNotValid(): void
    {
        static::assertFalse(Merchant\Order::create([], [], self::getGoodAuthentication()));
        try {
            static::assertFalse(Merchant\Order::createOrFail([], [], self::getGoodAuthentication()));
        } catch (\Exception $e) {
            static::assertRegExp('/OrderIsNotValid/', $e->getMessage());
        }
    }

    public function testCreateOrderValid(): void
    {
        static::assertNotFalse(Merchant\Order::create(self::getGoodPostParams(), [], self::getGoodAuthentication()));
    }

    public static function getGoodPostParams()
    {
        return [
            'order_id' => 'YOUR-CUSTOM-ORDER-ID-115',
            'price_amount' => 1000.99,
            'price_currency' => 'USD',
            'receive_currency' => 'EUR',
            'callback_url' => 'https://example.com/payments/callback?token=6tCENGUYI62ojkuzDPX7Jg',
            'cancel_url' => 'https://example.com/cart',
            'success_url' => 'https://example.com/account/orders',
            'title' => 'Order #112',
            'description' => 'Apple Iphone 6',
        ];
    }
}
