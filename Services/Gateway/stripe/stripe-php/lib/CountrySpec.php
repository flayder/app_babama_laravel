<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class CountrySpec.
 */
class CountrySpec extends ApiResource
{
    /**
     * This is a special case because the country specs endpoint has an
     *    underscore in it. The parent `className` function strips underscores.
     *
     * @return string the name of the class
     */
    public static function className()
    {
        return 'country_spec';
    }

    /**
     * @param array|string      $country the ISO country code of the country we
     *                                   retrieve the country specfication for, or an options array
     *                                   containing an `id` containing that code
     * @param array|string|null $opts
     *
     * @return CountrySpec
     */
    public static function retrieve($country, $opts = null)
    {
        return self::_retrieve($country, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of CountrySpecs
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }
}
