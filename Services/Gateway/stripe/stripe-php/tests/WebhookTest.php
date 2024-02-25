<?php

declare(strict_types=1);

namespace StripeJS;

class WebhookTest extends TestCase
{
    public const EVENT_PAYLOAD = '{
  "id": "evt_test_webhook",
  "object": "event"
}';
    public const SECRET = 'whsec_test_secret';

    private function generateHeader($opts = [])
    {
        $timestamp = \array_key_exists('timestamp', $opts) ? $opts['timestamp'] : time();
        $payload = \array_key_exists('payload', $opts) ? $opts['payload'] : self::EVENT_PAYLOAD;
        $secret = \array_key_exists('secret', $opts) ? $opts['secret'] : self::SECRET;
        $scheme = \array_key_exists('scheme', $opts) ? $opts['scheme'] : WebhookSignature::EXPECTED_SCHEME;
        $signature = \array_key_exists('signature', $opts) ? $opts['signature'] : null;
        if (null === $signature) {
            $signedPayload = "$timestamp.$payload";
            $signature = hash_hmac('sha256', $signedPayload, $secret);
        }

        return "t=$timestamp,$scheme=$signature";
    }

    public function testValidJsonAndHeader(): void
    {
        $sigHeader = $this->generateHeader();
        $event = Webhook::constructEvent(self::EVENT_PAYLOAD, $sigHeader, self::SECRET);
        static::assertEquals('evt_test_webhook', $event->id);
    }

    /** @expectedException \UnexpectedValueException */
    public function testInvalidJson(): void
    {
        $payload = 'this is not valid JSON';
        $sigHeader = $this->generateHeader(['payload' => $payload]);
        Webhook::constructEvent($payload, $sigHeader, self::SECRET);
    }

    /** @expectedException \StripeJS\Error\SignatureVerification */
    public function testValidJsonAndInvalidHeader(): void
    {
        $sigHeader = 'bad_header';
        Webhook::constructEvent(self::EVENT_PAYLOAD, $sigHeader, self::SECRET);
    }

    /**
     * @expectedException \StripeJS\Error\SignatureVerification
     *
     * @expectedExceptionMessage Unable to extract timestamp and signatures from header
     */
    public function testMalformedHeader(): void
    {
        $sigHeader = "i'm not even a real signature header";
        WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, $sigHeader, self::SECRET);
    }

    /**
     * @expectedException \StripeJS\Error\SignatureVerification
     *
     * @expectedExceptionMessage No signatures found with expected scheme
     */
    public function testNoSignaturesWithExpectedScheme(): void
    {
        $sigHeader = $this->generateHeader(['scheme' => 'v0']);
        WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, $sigHeader, self::SECRET);
    }

    /**
     * @expectedException \StripeJS\Error\SignatureVerification
     *
     * @expectedExceptionMessage No signatures found matching the expected signature for payload
     */
    public function testNoValidSignatureForPayload(): void
    {
        $sigHeader = $this->generateHeader(['signature' => 'bad_signature']);
        WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, $sigHeader, self::SECRET);
    }

    /**
     * @expectedException \StripeJS\Error\SignatureVerification
     *
     * @expectedExceptionMessage Timestamp outside the tolerance zone
     */
    public function testTimestampOutsideTolerance(): void
    {
        $sigHeader = $this->generateHeader(['timestamp' => time() - 15]);
        WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, $sigHeader, self::SECRET, 10);
    }

    public function testValidHeaderAndSignature(): void
    {
        $sigHeader = $this->generateHeader();
        static::assertTrue(WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, $sigHeader, self::SECRET, 10));
    }

    public function testHeaderContainsValidSignature(): void
    {
        $sigHeader = $this->generateHeader().',v1=bad_signature';
        static::assertTrue(WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, $sigHeader, self::SECRET, 10));
    }

    public function testTimestampOffButNoTolerance(): void
    {
        $sigHeader = $this->generateHeader(['timestamp' => 12345]);
        static::assertTrue(WebhookSignature::verifyHeader(self::EVENT_PAYLOAD, $sigHeader, self::SECRET));
    }
}
