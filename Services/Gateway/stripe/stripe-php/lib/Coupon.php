<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class Coupon.
 */
class Coupon extends ApiResource
{
    /**
     * @param array|string      $id   the ID of the coupon to retrieve, or an options
     *                                array containing an `id` key
     * @param array|string|null $opts
     *
     * @return Coupon
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Coupon the created coupon
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param string            $id      the ID of the coupon to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return Coupon the updated coupon
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Coupon the deleted coupon
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    /**
     * @param array|string|null $opts
     *
     * @return Coupon the saved coupon
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of Coupons
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }
}
