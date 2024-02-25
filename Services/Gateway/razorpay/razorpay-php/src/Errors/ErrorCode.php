<?php

declare(strict_types=1);

namespace Razorpay\Api\Errors;

class ErrorCode
{
    public const BAD_REQUEST_ERROR = 'BAD_REQUEST_ERROR';
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const GATEWAY_ERROR = 'GATEWAY_ERROR';

    public static function exists($code)
    {
        $code = strtoupper($code);

        return \defined(__CLASS__.'::'.$code);
    }
}
