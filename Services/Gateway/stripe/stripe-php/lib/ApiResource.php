<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class ApiResource.
 */
abstract class ApiResource extends StripeJSObject
{
    private static $HEADERS_TO_PERSIST = ['StripeJS-Account' => true, 'StripeJS-Version' => true];

    public static function baseUrl()
    {
        return StripeJS::$apiBase;
    }

    /** @return ApiResource the refreshed resource */
    public function refresh()
    {
        $requestor = new ApiRequestor($this->_opts->apiKey, static::baseUrl());
        $url = $this->instanceUrl();

        [$response, $this->_opts->apiKey] = $requestor->request(
            'get',
            $url,
            $this->_retrieveOptions,
            $this->_opts->headers
        );
        $this->setLastResponse($response);
        $this->refreshFrom($response->json, $this->_opts);

        return $this;
    }

    /**
     * @return string the name of the class, with namespacing and underscores
     *                stripped
     */
    public static function className()
    {
        $class = static::class;
        // Useful for namespaces: Foo\Charge
        if ($postfixNamespaces = strrchr($class, '\\')) {
            $class = substr($postfixNamespaces, 1);
        }
        // Useful for underscored 'namespaces': Foo_Charge
        if ($postfixFakeNamespaces = strrchr($class, '')) {
            $class = $postfixFakeNamespaces;
        }
        if ('StripeJS' == substr($class, 0, \strlen('StripeJS'))) {
            $class = substr($class, \strlen('StripeJS'));
        }
        $class = str_replace('_', '', $class);
        $name = urlencode($class);
        $name = strtolower($name);

        return $name;
    }

    /** @return string the endpoint URL for the given class */
    public static function classUrl()
    {
        $base = static::className();

        return "/v1/{$base}s";
    }

    /** @return string the instance endpoint URL for the given class */
    public static function resourceUrl($id)
    {
        if (null === $id) {
            $class = static::class;
            $message = 'Could not determine which URL to request: '
               ."$class instance has invalid ID: $id";
            throw new Error\InvalidRequest($message, null);
        }
        $id = Util\Util::utf8($id);
        $base = static::classUrl();
        $extn = urlencode($id);

        return "$base/$extn";
    }

    /** @return string the full API URL for this API resource */
    public function instanceUrl()
    {
        return static::resourceUrl($this['id']);
    }

    protected static function _validateParams($params = null): void
    {
        if ($params && !\is_array($params)) {
            $message = 'You must pass an array as the first argument to StripeJS API '
               .'method calls.  (HINT: an example call to create a charge '
               ."would be: \"StripeJS\\Charge::create(array('amount' => 100, "
               ."'currency' => 'usd', 'card' => array('number' => "
               ."4242424242424242, 'exp_month' => 5, 'exp_year' => 2015)))\")";
            throw new Error\Api($message);
        }
    }

    protected function _request($method, $url, $params = [], $options = null)
    {
        $opts = $this->_opts->merge($options);
        [$resp, $options] = static::_staticRequest($method, $url, $params, $opts);
        $this->setLastResponse($resp);

        return [$resp->json, $options];
    }

    protected static function _staticRequest($method, $url, $params, $options)
    {
        $opts = Util\RequestOptions::parse($options);
        $requestor = new ApiRequestor($opts->apiKey, static::baseUrl());
        [$response, $opts->apiKey] = $requestor->request($method, $url, $params, $opts->headers);
        foreach ($opts->headers as $k => $v) {
            if (!\array_key_exists($k, self::$HEADERS_TO_PERSIST)) {
                unset($opts->headers[$k]);
            }
        }

        return [$response, $opts];
    }

    protected static function _retrieve($id, $options = null)
    {
        $opts = Util\RequestOptions::parse($options);
        $instance = new static($id, $opts);
        $instance->refresh();

        return $instance;
    }

    protected static function _all($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        [$response, $opts] = static::_staticRequest('get', $url, $params, $options);
        $obj = Util\Util::convertToStripeJSObject($response->json, $opts);
        if (!is_a($obj, 'StripeJS\\Collection')) {
            $class = $obj::class;
            $message = "Expected type \"StripeJS\\Collection\", got \"$class\" instead";
            throw new Error\Api($message);
        }
        $obj->setLastResponse($response);
        $obj->setRequestParams($params);

        return $obj;
    }

    protected static function _create($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        [$response, $opts] = static::_staticRequest('post', $url, $params, $options);
        $obj = Util\Util::convertToStripeJSObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }

    /**
     * @param string            $id     the ID of the API resource to update
     * @param array|null        $params
     * @param array|string|null $opts
     *
     * @return ApiResource the updated API resource
     */
    protected static function _update($id, $params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::resourceUrl($id);

        [$response, $opts] = static::_staticRequest('post', $url, $params, $options);
        $obj = Util\Util::convertToStripeJSObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }

    protected function _save($options = null)
    {
        $params = $this->serializeParameters();
        if (\count($params) > 0) {
            $url = $this->instanceUrl();
            [$response, $opts] = $this->_request('post', $url, $params, $options);
            $this->refreshFrom($response, $opts);
        }

        return $this;
    }

    protected function _delete($params = null, $options = null)
    {
        self::_validateParams($params);

        $url = $this->instanceUrl();
        [$response, $opts] = $this->_request('delete', $url, $params, $options);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
