<?php

declare(strict_types=1);

namespace StripeJS;

use StripeJS\HttpClient\CurlClient;

class CurlClientTest extends TestCase
{
    public function testTimeout(): void
    {
        $curl = new CurlClient();
        static::assertSame(CurlClient::DEFAULT_TIMEOUT, $curl->getTimeout());
        static::assertSame(CurlClient::DEFAULT_CONNECT_TIMEOUT, $curl->getConnectTimeout());

        // implicitly tests whether we're returning the CurlClient instance
        $curl = $curl->setConnectTimeout(1)->setTimeout(10);
        static::assertSame(1, $curl->getConnectTimeout());
        static::assertSame(10, $curl->getTimeout());

        $curl->setTimeout(-1);
        $curl->setConnectTimeout(-999);
        static::assertSame(0, $curl->getTimeout());
        static::assertSame(0, $curl->getConnectTimeout());
    }

    public function testDefaultOptions(): void
    {
        // make sure options array loads/saves properly
        $optionsArray = [\CURLOPT_PROXY => 'localhost:80'];
        $withOptionsArray = new CurlClient($optionsArray);
        static::assertSame($withOptionsArray->getDefaultOptions(), $optionsArray);

        // make sure closure-based options work properly, including argument passing
        $ref = null;
        $withClosure = new CurlClient(function ($method, $absUrl, $headers, $params, $hasFile) use (&$ref) {
            $ref = \func_get_args();

            return [];
        });

        $withClosure->request('get', 'https://httpbin.org/status/200', [], [], false);
        static::assertSame($ref, ['get', 'https://httpbin.org/status/200', [], [], false]);

        // this is the last test case that will run, since it'll throw an exception at the end
        $withBadClosure = new CurlClient(fn () => 'thisShouldNotWork');
        $this->setExpectedException('StripeJS\Error\Api', 'Non-array value returned by defaultOptions CurlClient callback');
        $withBadClosure->request('get', 'https://httpbin.org/status/200', [], [], false);
    }

    public function testEncode(): void
    {
        $a = [
            'my' => 'value',
            'that' => ['your' => 'example'],
            'bar' => 1,
            'baz' => null,
        ];

        $enc = CurlClient::encode($a);
        static::assertSame('my=value&that%5Byour%5D=example&bar=1', $enc);

        $a = ['that' => ['your' => 'example', 'foo' => null]];
        $enc = CurlClient::encode($a);
        static::assertSame('that%5Byour%5D=example', $enc);

        $a = ['that' => 'example', 'foo' => ['bar', 'baz']];
        $enc = CurlClient::encode($a);
        static::assertSame('that=example&foo%5B%5D=bar&foo%5B%5D=baz', $enc);

        $a = [
            'my' => 'value',
            'that' => ['your' => ['cheese', 'whiz', null]],
            'bar' => 1,
            'baz' => null,
        ];

        $enc = CurlClient::encode($a);
        $expected = 'my=value&that%5Byour%5D%5B%5D=cheese'
              .'&that%5Byour%5D%5B%5D=whiz&bar=1';
        static::assertSame($expected, $enc);

        // Ignores an empty array
        $enc = CurlClient::encode(['foo' => [], 'bar' => 'baz']);
        $expected = 'bar=baz';
        static::assertSame($expected, $enc);

        $a = ['foo' => [['bar' => 'baz'], ['bar' => 'bin']]];
        $enc = CurlClient::encode($a);
        static::assertSame('foo%5B0%5D%5Bbar%5D=baz&foo%5B1%5D%5Bbar%5D=bin', $enc);
    }

    public function testSslOption(): void
    {
        // make sure options array loads/saves properly
        $optionsArray = [\CURLOPT_SSLVERSION => \CURL_SSLVERSION_TLSv1];
        $withOptionsArray = new CurlClient($optionsArray);
        static::assertSame($withOptionsArray->getDefaultOptions(), $optionsArray);
    }
}
