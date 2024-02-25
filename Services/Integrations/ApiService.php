<?php

namespace App\Services\Integrations;

use App\Exceptions\ApiRequestException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApiService
{
    private PendingRequest $client;

    public function __construct(PendingRequest $client)
    {
        $this->client = $client;
    }

    /**
     * @param ApiRequestData $data
     * @return array
     * @throws RequestException|ApiRequestException
     */
    public function postRequest(ApiRequestData $data): array
    {
        $this->logRequest($data);

        $response = $this->client->post($data->url, $data->payload);

        return $this->handleResponse($response);
    }

    /**
     * @param ApiRequestData $data
     * @return array
     * @throws RequestException|ApiRequestException
     */
    public function getRequest(ApiRequestData $data): array
    {
        $this->logRequest($data);

        $response = $this->client->get($data->url, $data->payload);

        return $this->handleResponse($response);
    }

    /**
     * @param Response $response
     * @return array
     * @throws RequestException|ApiRequestException
     */
    private function handleResponse(Response $response): array
    {
        if ($response->failed()) {
            $this->logFailed($response);

            if ($response->status() === 422 || $response->status() === 400) {
                throw new ApiRequestException($response);
            }
            $response->throw();
        }

        $this->logResponse($response);

        return $response->json();
    }

    private function logResponse(Response $response): void
    {
        Log::debug(
            'ORS response',
            [
                'reason' => $response->reason(),
                'status' => $response->status(),
                'json' => $response->json(),
                'body' => Str::substr($response->body(), 0, 100),
            ]
        );
    }

    private function logRequest(ApiRequestData $data): void
    {
        Log::debug(
            'ORS request',
            [
                'url' => config('services.ors.api_url') . $data->url,
                'method' => $data->method,
                'data' => json_encode($data->payload)
            ]
        );
    }

    private function logFailed(Response $response): void
    {
        Log::warning(
            'API request failed',
            [
                'reason' => $response->reason(),
                'status' => $response->status(),
                'body' => Str::substr($response->body(), 0, 100),
            ]
        );
    }
}
