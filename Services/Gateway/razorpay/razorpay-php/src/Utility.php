<?php

declare(strict_types=1);

namespace Razorpay\Api;

class Utility
{
    public const SHA256 = 'sha256';

    public function verifyPaymentSignature($attributes): void
    {
        $actualSignature = $attributes['razorpay_signature'];

        $paymentId = $attributes['razorpay_payment_id'];

        if (true === isset($attributes['razorpay_order_id'])) {
            $orderId = $attributes['razorpay_order_id'];

            $payload = $orderId.'|'.$paymentId;
        } elseif (true === isset($attributes['razorpay_subscription_id'])) {
            $subscriptionId = $attributes['razorpay_subscription_id'];

            $payload = $paymentId.'|'.$subscriptionId;
        } else {
            throw new Errors\SignatureVerificationError('Either razorpay_order_id or razorpay_subscription_id must be present.');
        }

        $secret = Api::getSecret();

        self::verifySignature($payload, $actualSignature, $secret);
    }

    public function verifyWebhookSignature($payload, $actualSignature, $secret): void
    {
        self::verifySignature($payload, $actualSignature, $secret);
    }

    public function verifySignature($payload, $actualSignature, $secret): void
    {
        $expectedSignature = hash_hmac(self::SHA256, $payload, $secret);

        // Use lang's built-in hash_equals if exists to mitigate timing attacks
        if (\function_exists('hash_equals')) {
            $verified = hash_equals($expectedSignature, $actualSignature);
        } else {
            $verified = $this->hashEquals($expectedSignature, $actualSignature);
        }

        if (false === $verified) {
            throw new Errors\SignatureVerificationError('Invalid signature passed');
        }
    }

    private function hashEquals($expectedSignature, $actualSignature)
    {
        if (\strlen($expectedSignature) === \strlen($actualSignature)) {
            $res = $expectedSignature ^ $actualSignature;
            $return = 0;

            for ($i = \strlen($res) - 1; $i >= 0; --$i) {
                $return |= \ord($res[$i]);
            }

            return 0 === $return;
        }

        return false;
    }
}
