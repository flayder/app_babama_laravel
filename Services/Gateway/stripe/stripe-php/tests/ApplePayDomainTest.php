<?php

declare(strict_types=1);

namespace StripeJS;

class ApplePayDomainTest extends TestCase
{
    public function testCreation(): void
    {
        $this->mockRequest(
            'POST',
            '/v1/apple_pay/domains',
            ['domain_name' => 'test.com'],
            [
                'id' => 'apwc_create',
                'object' => 'apple_pay_domain',
            ]
        );
        $d = ApplePayDomain::create([
            'domain_name' => 'test.com',
        ]);
        static::assertSame('apwc_create', $d->id);
        static::assertInstanceOf('StripeJS\\ApplePayDomain', $d);
    }

    public function testRetrieve(): void
    {
        $this->mockRequest(
            'GET',
            '/v1/apple_pay/domains/apwc_retrieve',
            [],
            [
                'id' => 'apwc_retrieve',
                'object' => 'apple_pay_domain',
            ]
        );
        $d = ApplePayDomain::retrieve('apwc_retrieve');
        static::assertSame('apwc_retrieve', $d->id);
        static::assertInstanceOf('StripeJS\\ApplePayDomain', $d);
    }

    public function testDeletion(): void
    {
        self::authorizeFromEnv();
        $d = ApplePayDomain::create([
            'domain_name' => 'jackshack.website',
        ]);
        static::assertInstanceOf('StripeJS\\ApplePayDomain', $d);
        $this->mockRequest(
            'DELETE',
            '/v1/apple_pay/domains/'.$d->id,
            [],
            ['deleted' => true]
        );
        $d->delete();
        static::assertTrue($d->deleted);
    }

    public function testList(): void
    {
        $this->mockRequest(
            'GET',
            '/v1/apple_pay/domains',
            [],
            [
                'url' => '/v1/apple_pay/domains',
                'object' => 'list',
            ]
        );
        $all = ApplePayDomain::all();
        static::assertSame($all->url, '/v1/apple_pay/domains');
    }
}
