<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class Transfer.
 *
 * @property string $id
 * @property string $object
 * @property int    $amount
 * @property int    $amount_reversed
 * @property string $balance_transaction
 * @property int    $created
 * @property string $currency
 * @property int    $date
 * @property mixed  $destination
 * @property mixed  $destination_payment
 * @property bool   $livemode
 * @property mixed  $metadata
 * @property mixed  $reversals
 * @property bool   $reversed
 * @property mixed  $source_transaction
 */
class Transfer extends ApiResource
{
    /**
     * @param array|string      $id   the ID of the transfer to retrieve, or an
     *                                options array containing an `id` key
     * @param array|string|null $opts
     *
     * @return Transfer
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of Transfers
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Transfer the created transfer
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param string            $id      the ID of the transfer to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return Transfer the updated transfer
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /** @return TransferReversal the created transfer reversal */
    public function reverse($params = null, $opts = null)
    {
        $url = $this->instanceUrl().'/reversals';
        [$response, $opts] = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }

    /** @return Transfer the canceled transfer */
    public function cancel()
    {
        $url = $this->instanceUrl().'/cancel';
        [$response, $opts] = $this->_request('post', $url);
        $this->refreshFrom($response, $opts);

        return $this;
    }

    /**
     * @param array|string|null $opts
     *
     * @return Transfer the saved transfer
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }
}
