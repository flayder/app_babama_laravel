<?php

declare(strict_types=1);

namespace StripeJS;

/**
 * Class ApiRequestor.
 */
class ApiRequestor
{
    private $_apiKey;

    private $_apiBase;

    private static $_httpClient;

    public function __construct($apiKey = null, $apiBase = null)
    {
        $this->_apiKey = $apiKey;
        if (!$apiBase) {
            $apiBase = StripeJS::$apiBase;
        }
        $this->_apiBase = $apiBase;
    }

    private static function _encodeObjects($d)
    {
        if ($d instanceof ApiResource) {
            return Util\Util::utf8($d->id);
        } elseif (true === $d) {
            return 'true';
        } elseif (false === $d) {
            return 'false';
        } elseif (\is_array($d)) {
            $res = [];
            foreach ($d as $k => $v) {
                $res[$k] = self::_encodeObjects($v);
            }

            return $res;
        } else {
            return Util\Util::utf8($d);
        }
    }

    /**
     * @param string     $method
     * @param string     $url
     * @param array|null $params
     * @param array|null $headers
     *
     * @return array an array whose first element is an API response and second
     *               element is the API key used to make the request
     */
    public function request($method, $url, $params = null, $headers = null)
    {
        if (!$params) {
            $params = [];
        }
        if (!$headers) {
            $headers = [];
        }
        [$rbody, $rcode, $rheaders, $myApiKey] =
        $this->_requestRaw($method, $url, $params, $headers);
        $json = $this->_interpretResponse($rbody, $rcode, $rheaders);
        $resp = new ApiResponse($rbody, $rcode, $rheaders, $json);

        return [$resp, $myApiKey];
    }

    /**
     * @param string $rbody    a JSON string
     * @param int    $rcode
     * @param array  $rheaders
     * @param array  $resp
     *
     * @throws Error\InvalidRequest if the error is caused by the user
     * @throws Error\Authentication if the error is caused by a lack of
     *                              permissions
     * @throws Error\Permission     if the error is caused by insufficient
     *                              permissions
     * @throws Error\Card           if the error is the error code is 402 (payment
     *                              required)
     * @throws Error\RateLimit      if the error is caused by too many requests
     *                              hitting the API
     * @throws Error\Api            otherwise
     */
    public function handleApiError($rbody, $rcode, $rheaders, $resp): void
    {
        if (!\is_array($resp) || !isset($resp['error'])) {
            $msg = "Invalid response object from API: $rbody "
              ."(HTTP response code was $rcode)";
            throw new Error\Api($msg, $rcode, $rbody, $resp, $rheaders);
        }

        $error = $resp['error'];
        $msg = $error['message'] ?? null;
        $param = $error['param'] ?? null;
        $code = $error['code'] ?? null;

        switch ($rcode) {
            case 400:
                // 'rate_limit' code is deprecated, but left here for backwards compatibility
                // for API versions earlier than 2015-09-08
                if ('rate_limit' == $code) {
                    throw new Error\RateLimit($msg, $param, $rcode, $rbody, $resp, $rheaders);
                }

                // intentional fall-through
                // no break
            case 404:
                throw new Error\InvalidRequest($msg, $param, $rcode, $rbody, $resp, $rheaders);
            case 401:
                throw new Error\Authentication($msg, $rcode, $rbody, $resp, $rheaders);
            case 402:
                throw new Error\Card($msg, $param, $code, $rcode, $rbody, $resp, $rheaders);
            case 403:
                throw new Error\Permission($msg, $rcode, $rbody, $resp, $rheaders);
            case 429:
                throw new Error\RateLimit($msg, $param, $rcode, $rbody, $resp, $rheaders);
            default:
                throw new Error\Api($msg, $rcode, $rbody, $resp, $rheaders);
        }
    }

    private static function _formatAppInfo($appInfo)
    {
        if (null !== $appInfo) {
            $string = $appInfo['name'];
            if (null !== $appInfo['version']) {
                $string .= '/'.$appInfo['version'];
            }
            if (null !== $appInfo['url']) {
                $string .= ' ('.$appInfo['url'].')';
            }

            return $string;
        } else {
            return null;
        }
    }

    private static function _defaultHeaders($apiKey)
    {
        $appInfo = StripeJS::getAppInfo();

        $uaString = 'StripeJS/v1 PhpBindings/'.StripeJS::VERSION;

        $langVersion = \PHP_VERSION;
        $uname = php_uname();

        $httplib = 'unknown';
        $ssllib = 'unknown';

        if (\function_exists('curl_version')) {
            $curlVersion = curl_version();
            $httplib = 'curl '.$curlVersion['version'];
            $ssllib = $curlVersion['ssl_version'];
        }

        $appInfo = StripeJS::getAppInfo();
        $ua = [
            'bindings_version' => StripeJS::VERSION,
            'lang' => 'php',
            'lang_version' => $langVersion,
            'publisher' => 'StripeJS',
            'uname' => $uname,
            'httplib' => $httplib,
            'ssllib' => $ssllib,
        ];
        if (null !== $appInfo) {
            $uaString .= ' '.self::_formatAppInfo($appInfo);
            $ua['application'] = $appInfo;
        }

        $defaultHeaders = [
            'X-StripeJS-Client-User-Agent' => json_encode($ua),
            'User-Agent' => $uaString,
            'Authorization' => 'Bearer '.$apiKey,
        ];

        return $defaultHeaders;
    }

    private function _requestRaw($method, $url, $params, $headers)
    {
        $myApiKey = $this->_apiKey;
        if (!$myApiKey) {
            $myApiKey = StripeJS::$apiKey;
        }

        if (!$myApiKey) {
            $msg = 'No API key provided.  (HINT: set your API key using '
              .'"StripeJS::setApiKey(<API-KEY>)".  You can generate API keys from '
              .'the StripeJS web interface.  See https://stripe.com/api for '
              .'details, or email support@stripe.com if you have any questions.';
            throw new Error\Authentication($msg);
        }

        $absUrl = $this->_apiBase.$url;
        $params = self::_encodeObjects($params);
        $defaultHeaders = $this->_defaultHeaders($myApiKey);
        if (StripeJS::$apiVersion) {
            $defaultHeaders['StripeJS-Version'] = StripeJS::$apiVersion;
        }

        if (StripeJS::$accountId) {
            $defaultHeaders['StripeJS-Account'] = StripeJS::$accountId;
        }

        $hasFile = false;
        $hasCurlFile = class_exists('\CURLFile', false);
        foreach ($params as $k => $v) {
            if (\is_resource($v)) {
                $hasFile = true;
                $params[$k] = self::_processResourceParam($v, $hasCurlFile);
            } elseif ($hasCurlFile && $v instanceof \CURLFile) {
                $hasFile = true;
            }
        }

        if ($hasFile) {
            $defaultHeaders['Content-Type'] = 'multipart/form-data';
        } else {
            $defaultHeaders['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $combinedHeaders = array_merge($defaultHeaders, $headers);
        $rawHeaders = [];

        foreach ($combinedHeaders as $header => $value) {
            $rawHeaders[] = $header.': '.$value;
        }

        [$rbody, $rcode, $rheaders] = $this->httpClient()->request(
            $method,
            $absUrl,
            $rawHeaders,
            $params,
            $hasFile
        );

        return [$rbody, $rcode, $rheaders, $myApiKey];
    }

    private function _processResourceParam($resource, $hasCurlFile)
    {
        if ('stream' !== get_resource_type($resource)) {
            throw new Error\Api('Attempted to upload a resource that is not a stream');
        }

        $metaData = stream_get_meta_data($resource);
        if ('plainfile' !== $metaData['wrapper_type']) {
            throw new Error\Api('Only plainfile resource streams are supported');
        }

        if ($hasCurlFile) {
            // We don't have the filename or mimetype, but the API doesn't care
            return new \CURLFile($metaData['uri']);
        } else {
            return '@'.$metaData['uri'];
        }
    }

    private function _interpretResponse($rbody, $rcode, $rheaders)
    {
        $resp = json_decode($rbody, true);
        $jsonError = json_last_error();
        if (null === $resp && \JSON_ERROR_NONE !== $jsonError) {
            $msg = "Invalid response body from API: $rbody "
              ."(HTTP response code was $rcode, json_last_error() was $jsonError)";
            throw new Error\Api($msg, $rcode, $rbody);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleApiError($rbody, $rcode, $rheaders, $resp);
        }

        return $resp;
    }

    public static function setHttpClient($client): void
    {
        self::$_httpClient = $client;
    }

    private function httpClient()
    {
        if (!self::$_httpClient) {
            self::$_httpClient = HttpClient\CurlClient::instance();
        }

        return self::$_httpClient;
    }
}
