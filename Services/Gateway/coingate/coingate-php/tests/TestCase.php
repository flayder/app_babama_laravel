<?php

declare(strict_types=1);

namespace CoinGate;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public const AUTH_TOKEN = '-39RHqyAiyBmpwAEz9FcFxcVZDqbGmvKXTdHztny';
    public const ENVIRONMENT = 'sandbox';

    public static function getGoodAuthentication()
    {
        return [
            'auth_token' => self::AUTH_TOKEN,
            'environment' => self::ENVIRONMENT,
        ];
    }
}
