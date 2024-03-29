<?php

declare(strict_types=1);
/**
 * Exception for unknown status responses.
 */

/**
 * Exception for unknown status responses.
 */
class Requests_Exception_HTTP_Unknown extends Requests_Exception_HTTP
{
    /**
     * HTTP status code.
     *
     * @var int|bool Code if available, false if an error occurred
     */
    protected $code = 0;

    /**
     * Reason phrase.
     *
     * @var string
     */
    protected $reason = 'Unknown';

    /**
     * Create a new exception.
     *
     * If `$data` is an instance of {@see Requests_Response}, uses the status
     * code from it. Otherwise, sets as 0
     *
     * @param string|null $reason Reason phrase
     * @param mixed       $data   Associated data
     */
    public function __construct($reason = null, $data = null)
    {
        if ($data instanceof Requests_Response) {
            $this->code = $data->status_code;
        }

        parent::__construct($reason, $data);
    }
}
