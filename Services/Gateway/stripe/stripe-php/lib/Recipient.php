<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class Recipient.
 */
class Recipient extends ApiResource
{
    /**
     * @param array|string      $id   the ID of the recipient to retrieve, or an
     *                                options array containing an `id` key
     * @param array|string|null $opts
     *
     * @return Recipient
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of Recipients
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Recipient the created recipient
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param string            $id      the ID of the recipient to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return Recipient the updated recipient
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /**
     * @param array|string|null $opts
     *
     * @return Recipient the saved recipient
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    /**
     * @param array|null $params
     *
     * @return Recipient the deleted recipient
     */
    public function delete($params = null, $opts = null)
    {
        return $this->_delete($params, $opts);
    }

    /**
     * @param array|null $params
     *
     * @return Collection of the Recipient's Transfers
     */
    public function transfers($params = null)
    {
        if (null === $params) {
            $params = [];
        }
        $params['recipient'] = $this->id;
        $transfers = Transfer::all($params, $this->_opts);

        return $transfers;
    }
}
