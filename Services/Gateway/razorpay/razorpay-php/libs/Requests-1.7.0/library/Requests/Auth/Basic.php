<?php

declare(strict_types=1);
/**
 * Basic Authentication provider.
 */

/**
 * Basic Authentication provider.
 *
 * Provides a handler for Basic HTTP authentication via the Authorization
 * header.
 */
class Requests_Auth_Basic implements Requests_Auth
{
    /**
     * Username.
     *
     * @var string
     */
    public $user;

    /**
     * Password.
     *
     * @var string
     */
    public $pass;

    /**
     * Constructor.
     *
     * @param array|null $args Array of user and password. Must have exactly two elements
     *
     * @throws Requests_Exception On incorrect number of arguments (`authbasicbadargs`)
     */
    public function __construct($args = null)
    {
        if (is_array($args)) {
            if (2 !== count($args)) {
                throw new Requests_Exception('Invalid number of arguments', 'authbasicbadargs');
            }

            [$this->user, $this->pass] = $args;
        }
    }

    /**
     * Register the necessary callbacks.
     *
     * @see curl_before_send
     * @see fsockopen_header
     *
     * @param Requests_Hooks $hooks Hook system
     */
    public function register(Requests_Hooks &$hooks): void
    {
        $hooks->register('curl.before_send', [&$this, 'curl_before_send']);
        $hooks->register('fsockopen.after_headers', [&$this, 'fsockopen_header']);
    }

    /**
     * Set cURL parameters before the data is sent.
     *
     * @param resource $handle cURL resource
     */
    public function curl_before_send(&$handle): void
    {
        curl_setopt($handle, \CURLOPT_HTTPAUTH, \CURLAUTH_BASIC);
        curl_setopt($handle, \CURLOPT_USERPWD, $this->getAuthString());
    }

    /**
     * Add extra headers to the request before sending.
     *
     * @param string $out HTTP header string
     */
    public function fsockopen_header(&$out): void
    {
        $out .= sprintf("Authorization: Basic %s\r\n", base64_encode($this->getAuthString()));
    }

    /**
     * Get the authentication string (user:pass).
     *
     * @return string
     */
    public function getAuthString()
    {
        return $this->user.':'.$this->pass;
    }
}
