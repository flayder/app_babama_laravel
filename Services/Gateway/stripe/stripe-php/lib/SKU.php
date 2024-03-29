<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class SKU.
 */
class SKU extends ApiResource
{
    /**
     * @param array|string      $id   the ID of the SKU to retrieve, or an options
     *                                array containing an `id` key
     * @param array|string|null $opts
     *
     * @return SKU
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return SKU the created SKU
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param string            $id      the ID of the SKU to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return SKU the updated SKU
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /**
     * @param array|string|null $opts
     *
     * @return SKU the saved SKU
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of SKUs
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return SKU the deleted sku
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }
}
