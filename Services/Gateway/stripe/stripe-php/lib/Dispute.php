<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class Dispute.
 *
 * @property string $id
 * @property string $object
 * @property int    $amount
 * @property mixed  $balance_transactions
 * @property string $charge
 * @property int    $created
 * @property string $currency
 * @property mixed  $evidence
 * @property mixed  $evidence_details
 * @property bool   $is_charge_refundable
 * @property bool   $livemode
 * @property mixed  $metadata
 * @property string $reason
 * @property string $status
 */
class Dispute extends ApiResource
{
    /**
     * @param array|string      $id      the ID of the dispute to retrieve, or an options
     *                                   array containing an `id` key
     * @param array|string|null $options
     *
     * @return Dispute
     */
    public static function retrieve($id, $options = null)
    {
        return self::_retrieve($id, $options);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return array an array of Disputes
     */
    public static function all($params = null, $options = null)
    {
        return self::_all($params, $options);
    }

    /**
     * @param string            $id      the ID of the dispute to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return Dispute the updated dispute
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /**
     * @param array|string|null $options
     *
     * @return Dispute the saved charge
     */
    public function save($options = null)
    {
        return $this->_save($options);
    }

    /**
     * @param array|string|null $options
     *
     * @return Dispute the closed dispute
     */
    public function close($options = null)
    {
        $url = $this->instanceUrl().'/close';
        [$response, $opts] = $this->_request('post', $url, null, $options);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
