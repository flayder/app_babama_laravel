<?php

namespace App\Exceptions;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

class ApiRequestException extends HttpClientException
{
    protected int $truncateAt = 400;

    /**
     * The response instance.
     *
     * @var \Illuminate\Http\Client\Response
     */
    public $response;

    /**
     * Create a new exception instance.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return void
     */
    public function __construct(Response $response)
    {
        parent::__construct($this->prepareMessage($response), $response->status());

        $this->response = $response;
    }

    /**
     * Prepare the exception message.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return string
     */
    protected function prepareMessage(Response $response)
    {
        $message = "status code {$response->status()}";
        $summary = Str::substr($response->getBody(), 0, $this->truncateAt);

        return is_null($summary) ? $message : $message .= ":\n{$summary}\n";
    }
}
