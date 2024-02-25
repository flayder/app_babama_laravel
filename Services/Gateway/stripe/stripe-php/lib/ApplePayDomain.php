<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class ApplePayDomain.
 */
class ApplePayDomain extends ApiResource
{
    /**
     * @return string The class URL for this resource. It needs to be special
     *                cased because it doesn't fit into the standard resource pattern.
     */
    public static function classUrl()
    {
        return '/v1/apple_pay/domains';
    }

    /**
     * @param array|string      $id   the ID of the domain to retrieve, or an options
     *                                array containing an `id` key
     * @param array|string|null $opts
     *
     * @return ApplePayDomain
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return ApplePayDomain the created domain
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return ApplePayDomain the deleted domain
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of ApplePayDomains
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }
}
