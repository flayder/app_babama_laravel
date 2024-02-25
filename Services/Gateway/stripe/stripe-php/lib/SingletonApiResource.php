<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class SingletonApiResource.
 */
abstract class SingletonApiResource extends ApiResource
{
    protected static function _singletonRetrieve($options = null)
    {
        $opts = Util\RequestOptions::parse($options);
        $instance = new static(null, $opts);
        $instance->refresh();

        return $instance;
    }

    /** @return string the endpoint associated with this singleton class */
    public static function classUrl()
    {
        $base = static::className();

        return "/v1/{$base}";
    }

    /** @return string the endpoint associated with this singleton API resource */
    public function instanceUrl()
    {
        return static::classUrl();
    }
}
