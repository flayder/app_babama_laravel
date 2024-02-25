<?php

declare(strict_types=1);

class Requests_Exception_Transport_cURL extends Requests_Exception_Transport
{
    public const EASY = 'cURLEasy';
    public const MULTI = 'cURLMulti';
    public const SHARE = 'cURLShare';

    /**
     * cURL error code.
     *
     * @var int
     */
    protected $code = -1;

    /**
     * Which type of cURL error.
     *
     * EASY|MULTI|SHARE
     *
     * @var string
     */
    protected $type = 'Unknown';

    /**
     * Clear text error message.
     *
     * @var string
     */
    protected $reason = 'Unknown';

    public function __construct($message, $type, $data = null, $code = 0)
    {
        if (null !== $type) {
            $this->type = $type;
        }

        if (null !== $code) {
            $this->code = $code;
        }

        if (null !== $message) {
            $this->reason = $message;
        }

        $message = sprintf('%d %s', $this->code, $this->reason);
        parent::__construct($message, $this->type, $data, $this->code);
    }

    /** Get the error message */
    public function getReason()
    {
        return $this->reason;
    }
}
