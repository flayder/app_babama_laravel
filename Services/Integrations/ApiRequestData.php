<?php

namespace App\Services\Integrations;

use Illuminate\Contracts\Support\Arrayable;

class ApiRequestData implements Arrayable
{
    public string $url;

    public array $payload;

    public string $method;

    /**
     * @param string $url
     * @param string $method
     * @param array  $payload
     */
    public function __construct(string $url, string $method, array $payload = [])
    {
        $this->url = $url;
        $this->method = $method;
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return json_decode(json_encode($this), true);
    }
}
