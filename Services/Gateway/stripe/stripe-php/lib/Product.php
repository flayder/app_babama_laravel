<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class Product.
 */
class Product extends ApiResource
{
    /**
     * @param array|string      $id   the ID of the product to retrieve, or an options
     *                                array contianing an `id` key
     * @param array|string|null $opts
     *
     * @return Product
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Product the created Product
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param string            $id      the ID of the product to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return Product the updated product
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /**
     * @param array|string|null $opts
     *
     * @return Product the saved Product
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of Products
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Product the deleted product
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }
}
