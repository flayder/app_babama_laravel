<?php

declare(strict_types=1);

namespace StripeJS;

class SourceTest extends TestCase
{
    public function testRetrieve(): void
    {
        $this->mockRequest(
            'GET',
            '/v1/sources/src_foo',
            [],
            [
                'id' => 'src_foo',
                'object' => 'source',
            ]
        );
        $source = Source::retrieve('src_foo');
        static::assertSame($source->id, 'src_foo');
    }

    public function testCreate(): void
    {
        $this->mockRequest(
            'POST',
            '/v1/sources',
            [
                'type' => 'bitcoin',
                'amount' => 1000,
                'currency' => 'usd',
                'owner' => ['email' => 'jenny.rosen@example.com'],
            ],
            [
                'id' => 'src_foo',
                'object' => 'source',
            ]
        );
        $source = Source::create([
            'type' => 'bitcoin',
            'amount' => 1000,
            'currency' => 'usd',
            'owner' => ['email' => 'jenny.rosen@example.com'],
        ]);
        static::assertSame($source->id, 'src_foo');
    }

    public function testSave(): void
    {
        $response = [
            'id' => 'src_foo',
            'object' => 'source',
            'metadata' => [],
        ];
        $this->mockRequest(
            'GET',
            '/v1/sources/src_foo',
            [],
            $response
        );

        $response['metadata'] = ['foo' => 'bar'];
        $this->mockRequest(
            'POST',
            '/v1/sources/src_foo',
            [
                'metadata' => ['foo' => 'bar'],
            ],
            $response
        );

        $source = Source::retrieve('src_foo');
        $source->metadata['foo'] = 'bar';
        $source->save();
        static::assertSame($source->metadata['foo'], 'bar');
    }

    public function testSaveOwner(): void
    {
        $response = [
            'id' => 'src_foo',
            'object' => 'source',
            'owner' => [
                'name' => null,
                'address' => null,
            ],
        ];
        $this->mockRequest(
            'GET',
            '/v1/sources/src_foo',
            [],
            $response
        );

        $response['owner'] = [
            'name' => 'StripeJSy McStripeJS',
            'address' => [
                'line1' => 'Test Address',
                'city' => 'Test City',
                'postal_code' => '12345',
                'state' => 'Test State',
                'country' => 'Test Country',
            ],
        ];
        $this->mockRequest(
            'POST',
            '/v1/sources/src_foo',
            [
                'owner' => [
                    'name' => 'StripeJSy McStripeJS',
                    'address' => [
                        'line1' => 'Test Address',
                        'city' => 'Test City',
                        'postal_code' => '12345',
                        'state' => 'Test State',
                        'country' => 'Test Country',
                    ],
                ],
            ],
            $response
        );

        $source = Source::retrieve('src_foo');
        $source->owner['name'] = 'StripeJSy McStripeJS';
        $source->owner['address'] = [
            'line1' => 'Test Address',
            'city' => 'Test City',
            'postal_code' => '12345',
            'state' => 'Test State',
            'country' => 'Test Country',
        ];
        $source->save();
        static::assertSame($source->owner['name'], 'StripeJSy McStripeJS');
        static::assertSame($source->owner['address']['line1'], 'Test Address');
        static::assertSame($source->owner['address']['city'], 'Test City');
        static::assertSame($source->owner['address']['postal_code'], '12345');
        static::assertSame($source->owner['address']['state'], 'Test State');
        static::assertSame($source->owner['address']['country'], 'Test Country');
    }

    public function testDeleteAttached(): void
    {
        $response = [
            'id' => 'src_foo',
            'object' => 'source',
            'customer' => 'cus_bar',
        ];
        $this->mockRequest(
            'GET',
            '/v1/sources/src_foo',
            [],
            $response
        );

        unset($response['customer']);
        $this->mockRequest(
            'DELETE',
            '/v1/customers/cus_bar/sources/src_foo',
            [],
            $response
        );

        $source = Source::retrieve('src_foo');
        $source->delete();
        static::assertFalse(\array_key_exists('customer', $source));
    }

    /** @expectedException StripeJS\Error\Api */
    public function testDeleteUnattached(): void
    {
        $response = [
            'id' => 'src_foo',
            'object' => 'source',
        ];
        $this->mockRequest(
            'GET',
            '/v1/sources/src_foo',
            [],
            $response
        );

        $source = Source::retrieve('src_foo');
        $source->delete();
    }

    public function testVerify(): void
    {
        $response = [
            'id' => 'src_foo',
            'object' => 'source',
            'verification' => ['status' => 'pending'],
        ];
        $this->mockRequest(
            'GET',
            '/v1/sources/src_foo',
            [],
            $response
        );

        $response['verification']['status'] = 'succeeded';
        $this->mockRequest(
            'POST',
            '/v1/sources/src_foo/verify',
            [
                'values' => [32, 45],
            ],
            $response
        );

        $source = Source::retrieve('src_foo');
        static::assertSame($source->verification->status, 'pending');
        $source->verify([
            'values' => [32, 45],
        ]);
        static::assertSame($source->verification->status, 'succeeded');
    }
}
