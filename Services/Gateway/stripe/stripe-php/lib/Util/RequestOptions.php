<?php

declare(strict_types=1);

namespace StripeJS\Util;

use StripeJS\Error;

class RequestOptions
{
    public $headers;
    public $apiKey;

    public function __construct($key = null, $headers = [])
    {
        $this->apiKey = $key;
        $this->headers = $headers;
    }

    /**
     * Unpacks an options array and merges it into the existing RequestOptions
     * object.
     *
     * @param array|string|null $options a key => value array
     *
     * @return RequestOptions
     */
    public function merge($options)
    {
        $other_options = self::parse($options);
        if (null === $other_options->apiKey) {
            $other_options->apiKey = $this->apiKey;
        }
        $other_options->headers = array_merge($this->headers, $other_options->headers);

        return $other_options;
    }

    /**
     * Unpacks an options array into an RequestOptions object.
     *
     * @param array|string|null $options a key => value array
     *
     * @return RequestOptions
     */
    public static function parse($options)
    {
        if ($options instanceof self) {
            return $options;
        }

        if (null === $options) {
            return new self(null, []);
        }

        if (\is_string($options)) {
            return new self($options, []);
        }

        if (\is_array($options)) {
            $headers = [];
            $key = null;
            if (\array_key_exists('api_key', $options)) {
                $key = $options['api_key'];
            }
            if (\array_key_exists('idempotency_key', $options)) {
                $headers['Idempotency-Key'] = $options['idempotency_key'];
            }
            if (\array_key_exists('StripeJS_account', $options)) {
                $headers['StripeJS-Account'] = $options['StripeJS_account'];
            }
            if (\array_key_exists('StripeJS_version', $options)) {
                $headers['StripeJS-Version'] = $options['StripeJS_version'];
            }

            return new self($key, $headers);
        }

        $message = 'The second argument to StripeJS API method calls is an '
           .'optional per-request apiKey, which must be a string, or '
           .'per-request options, which must be an array. (HINT: you can set '
           .'a global apiKey by "StripeJS::setApiKey(<apiKey>)")';
        throw new Error\Api($message);
    }
}
