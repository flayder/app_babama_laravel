<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class BitcoinReceiver.
 */
class BitcoinReceiver extends ExternalAccount
{
    /**
     * @return string The class URL for this resource. It needs to be special
     *                cased because it doesn't fit into the standard resource pattern.
     */
    public static function classUrl()
    {
        return '/v1/bitcoin/receivers';
    }

    /**
     * @return string The instance URL for this resource. It needs to be special
     *                cased because it doesn't fit into the standard resource pattern.
     */
    public function instanceUrl()
    {
        $result = parent::instanceUrl();
        if ($result) {
            return $result;
        } else {
            $id = $this['id'];
            $id = Util\Util::utf8($id);
            $extn = urlencode($id);
            $base = self::classUrl();

            return "$base/$extn";
        }
    }

    /**
     * @param array|string      $id   the ID of the bitcoin receiver to retrieve, or
     *                                an options array containing an `id` key
     * @param array|string|null $opts
     *
     * @return BitcoinReceiver
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of BitcoinReceivers
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return BitcoinReceiver the created Bitcoin Receiver item
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return BitcoinReceiver the refunded Bitcoin Receiver item
     */
    public function refund($params = null, $options = null)
    {
        $url = $this->instanceUrl().'/refund';
        [$response, $opts] = $this->_request('post', $url, $params, $options);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
