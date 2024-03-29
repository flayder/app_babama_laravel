<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class SubscriptionItem.
 */
class SubscriptionItem extends ApiResource
{
    /**
     * This is a special case because the subscription items endpoint has an
     *    underscore in it. The parent `className` function strips underscores.
     *
     * @return string the name of the class
     */
    public static function className()
    {
        return 'subscription_item';
    }

    /**
     * @param array|string      $id   the ID of the subscription item to retrieve, or
     *                                an options array containing an `id` key
     * @param array|string|null $opts
     *
     * @return SubscriptionItem
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of SubscriptionItems
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return SubscriptionItem the created subscription item
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param string            $id      the ID of the subscription item to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return SubscriptionItem the updated subscription item
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /**
     * @param array|string|null $opts
     *
     * @return SubscriptionItem the saved subscription item
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return SubscriptionItem the deleted subscription item
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }
}
