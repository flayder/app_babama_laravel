<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class StripeJS.
 */
class StripeJS
{
    // @var string The StripeJS API key to be used for requests.
    public static $apiKey;

    // @var string The base URL for the StripeJS API.
    public static $apiBase = 'https://api.stripe.com';

    // @var string The base URL for the StripeJS API uploads endpoint.
    public static $apiUploadBase = 'https://uploads.stripe.com';

    // @var string|null The version of the StripeJS API to use for requests.
    public static $apiVersion = null;

    // @var string|null The account ID for connected accounts requests.
    public static $accountId = null;

    // @var boolean Defaults to true.
    public static $verifySslCerts = true;

    // @var array The application's information (name, version, URL)
    public static $appInfo = null;

    // @var Util\LoggerInterface|null The logger to which the library will
    //   produce messages.
    public static $logger = null;

    public const VERSION = '4.10.0';

    /** @return string the API key used for requests */
    public static function getApiKey()
    {
        return self::$apiKey;
    }

    /**
     * @return Util\LoggerInterface the logger to which the library will
     *                              produce messages
     */
    public static function getLogger()
    {
        if (null == self::$logger) {
            return new Util\DefaultLogger();
        }

        return self::$logger;
    }

    /**
     * @param Util\LoggerInterface $logger the logger to which the library
     *                                     will produce messages
     */
    public static function setLogger($logger): void
    {
        self::$logger = $logger;
    }

    /**
     * Sets the API key to be used for requests.
     *
     * @param string $apiKey
     */
    public static function setApiKey($apiKey): void
    {
        self::$apiKey = $apiKey;
    }

    /**
     * @return string The API version used for requests. null if we're using the
     *                latest version.
     */
    public static function getApiVersion()
    {
        return self::$apiVersion;
    }

    /** @param string $apiVersion the API version to use for requests */
    public static function setApiVersion($apiVersion): void
    {
        self::$apiVersion = $apiVersion;
    }

    /** @return bool */
    public static function getVerifySslCerts()
    {
        return self::$verifySslCerts;
    }

    /** @param bool $verify */
    public static function setVerifySslCerts($verify): void
    {
        self::$verifySslCerts = $verify;
    }

    /**
     * @return string|null The StripeJS account ID for connected account
     *                     requests
     */
    public static function getAccountId()
    {
        return self::$accountId;
    }

    /**
     * @param string $accountId the StripeJS account ID to set for connected
     *                          account requests
     */
    public static function setAccountId($accountId): void
    {
        self::$accountId = $accountId;
    }

    /** @return array|null The application's information */
    public static function getAppInfo()
    {
        return self::$appInfo;
    }

    /**
     * @param string $appName    The application's name
     * @param string $appVersion The application's version
     * @param string $appUrl     The application's URL
     */
    public static function setAppInfo($appName, $appVersion = null, $appUrl = null): void
    {
        if (null === self::$appInfo) {
            self::$appInfo = [];
        }
        self::$appInfo['name'] = $appName;
        self::$appInfo['version'] = $appVersion;
        self::$appInfo['url'] = $appUrl;
    }
}
