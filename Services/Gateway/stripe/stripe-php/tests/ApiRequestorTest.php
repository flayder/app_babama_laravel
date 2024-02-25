<?php

declare(strict_types=1);

namespace StripeJS;

use StripeJS\HttpClient\CurlClient;

class ApiRequestorTest extends TestCase
{
    public function testEncodeObjects(): void
    {
        $reflector = new \ReflectionClass('StripeJS\\ApiRequestor');
        $method = $reflector->getMethod('_encodeObjects');
        $method->setAccessible(true);

        $a = ['customer' => new Customer('abcd')];
        $enc = $method->invoke(null, $a);
        static::assertSame($enc, ['customer' => 'abcd']);

        // Preserves UTF-8
        $v = ['customer' => '☃'];
        $enc = $method->invoke(null, $v);
        static::assertSame($enc, $v);

        // Encodes latin-1 -> UTF-8
        $v = ['customer' => "\xe9"];
        $enc = $method->invoke(null, $v);
        static::assertSame($enc, ['customer' => "\xc3\xa9"]);
    }

    public function testHttpClientInjection(): void
    {
        $reflector = new \ReflectionClass('StripeJS\\ApiRequestor');
        $method = $reflector->getMethod('httpClient');
        $method->setAccessible(true);

        $curl = new CurlClient();
        $curl->setTimeout(10);
        ApiRequestor::setHttpClient($curl);

        $injectedCurl = $method->invoke(new ApiRequestor());
        static::assertSame($injectedCurl, $curl);
    }

    public function testDefaultHeaders(): void
    {
        $reflector = new \ReflectionClass('StripeJS\\ApiRequestor');
        $method = $reflector->getMethod('_defaultHeaders');
        $method->setAccessible(true);

        // no way to stub static methods with PHPUnit 4.x :(
        StripeJS::setAppInfo('MyTestApp', '1.2.34', 'https://mytestapp.example');
        $apiKey = 'sk_test_notarealkey';

        $headers = $method->invoke(null, $apiKey);

        $ua = json_decode($headers['X-StripeJS-Client-User-Agent']);
        static::assertSame($ua->application->name, 'MyTestApp');
        static::assertSame($ua->application->version, '1.2.34');
        static::assertSame($ua->application->url, 'https://mytestapp.example');

        static::assertSame(
            $headers['User-Agent'],
            'StripeJS/v1 PhpBindings/'.StripeJS::VERSION.' MyTestApp/1.2.34 (https://mytestapp.example)'
        );

        static::assertSame($headers['Authorization'], 'Bearer '.$apiKey);
    }
}
