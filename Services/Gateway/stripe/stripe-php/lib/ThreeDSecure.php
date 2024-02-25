<?php

declare(strict_types=1);

namespace StripeJS;

class ThreeDSecure extends ApiResource
{
    /** @return string the endpoint URL for the given class */
    public static function classUrl()
    {
        return '/v1/3d_secure';
    }

    /**
     * @param array|string      $id      the ID of the 3DS auth to retrieve, or an
     *                                   options array contianing an `id` key
     * @param array|string|null $options
     *
     * @return ThreeDSecure
     */
    public static function retrieve($id, $options = null)
    {
        return self::_retrieve($id, $options);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return ThreeDSecure the created 3D Secure object
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }
}
