<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class Invoice.
 */
class Invoice extends ApiResource
{
    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Invoice the created invoice
     */
    public static function create($params = null, $opts = null)
    {
        return self::_create($params, $opts);
    }

    /**
     * @param array|string      $id   the ID of the invoice to retrieve, or an options
     *                                array containing an `id` key
     * @param array|string|null $opts
     *
     * @return Invoice
     */
    public static function retrieve($id, $opts = null)
    {
        return self::_retrieve($id, $opts);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Collection of Invoices
     */
    public static function all($params = null, $opts = null)
    {
        return self::_all($params, $opts);
    }

    /**
     * @param string            $id      the ID of the invoice to update
     * @param array|null        $params
     * @param array|string|null $options
     *
     * @return Invoice the updated invoice
     */
    public static function update($id, $params = null, $options = null)
    {
        return self::_update($id, $params, $options);
    }

    /**
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return Invoice the upcoming invoice
     */
    public static function upcoming($params = null, $opts = null)
    {
        $url = static::classUrl().'/upcoming';
        [$response, $opts] = static::_staticRequest('get', $url, $params, $opts);
        $obj = Util\Util::convertToStripeJSObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }

    /**
     * @param array|string|null $opts
     *
     * @return Invoice the saved invoice
     */
    public function save($opts = null)
    {
        return $this->_save($opts);
    }

    /** @return Invoice the paid invoice */
    public function pay($opts = null)
    {
        $url = $this->instanceUrl().'/pay';
        [$response, $opts] = $this->_request('post', $url, null, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
