<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class Balance.
 *
 * @property string $object
 * @property mixed  $available
 * @property bool   $livedmode
 * @property mixed  $pending
 */
class Balance extends SingletonApiResource
{
    /**
     * @param array|string|null $opts
     *
     * @return Balance
     */
    public static function retrieve($opts = null)
    {
        return self::_singletonRetrieve($opts);
    }
}
