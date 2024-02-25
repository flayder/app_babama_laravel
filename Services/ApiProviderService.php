<?php

namespace App\Services;

use App\Models\ApiProvider;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Client\HttpClientException;
use Ixudra\Curl\Facades\Curl;

class ApiProviderService
{
    public function makeOrder(Order $order, Service $service, array $options): void
    {
        $message = "Что то пошло не так попробуйте снова!";
        if (isset($service->api_provider_id)) {
            $responseFromProvider = $this->getResponseFromProvider($service, $options);

            if (isset($responseFromProvider->order)) {
                $order->status_description = "order: {$responseFromProvider->order}";
                $order->api_order_id = $responseFromProvider->order;
                $order->status = 'processing';
                $order->save();
                return;
            }

            $order->status_description = "Ошибка: {$responseFromProvider->error}";

            if ($responseFromProvider->error == "Incorrect request") {
                $message = "Неправильное заполнение данных";
                $order->status_description = "Ошибка: $message";
            }
        }

        throw new HttpClientException($message);
    }

    public function getResponseFromProvider(Service $service, array $options): mixed
    {
        $apiproviderdata = ApiProvider::query()->findOrFail($service->api_provider_id)->toArray();
        $postData = [
            'key' => $apiproviderdata['api_key'],
            'action' => 'add',
            'service' => $service->api_service_id,
            'link' => $options ['link'],
            'quantity' => $options['quantity'],
        ];

        if (isset($options['runs'])) {
            $postData['runs'] = $options['runs'];
        }

        if (isset($options['interval'])) {
            $postData['interval'] = $options['interval'];
        }

        $response = Curl::to($apiproviderdata['url'])->withData($postData)->post();

        return json_decode($response);
    }

}
