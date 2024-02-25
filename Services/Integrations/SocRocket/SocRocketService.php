<?php

namespace App\Services\Integrations\SocRocket;

use App\Contracts\Seller;
use App\Dto\Order\OrderDto;
use App\Dto\Seller\Balance\BalanceSellerSuccessResponseDto;
use App\Dto\Seller\Balance\BalanceSellerRequestDto;
use App\Dto\Seller\Services\ServicesSellerRequestDto;
use App\Dto\Seller\Services\ServicesSellerSuccessResponseDto;
use App\Dto\Seller\OrderStatus\OrderStatusSellerRequestDto;
use App\Dto\Seller\OrderStatus\OrderStatusSellerSuccessResponseDto;
use App\Dto\Seller\SellerFailedResponseDto;
use App\Dto\Seller\Send\SendSellerRequestDto;
use App\Dto\Seller\Send\SendSellerSuccessResponseDto;
use App\Dto\Seller\OrdersStatus\OrdersStatusSellerRequestDto;
use App\Services\Integrations\ApiRequestData;
use App\Services\Integrations\ApiService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с PartnerSoc
 */
class SocRocketService implements Seller
{
    private ApiService $apiService;
    private string $key;

    public function __construct()
    {
        $apiName = config('services.sellers.list.soc_rocket.api_name');

        $client = Http::$apiName();

        $this->key = $client->getOptions()['key'];

        $this->apiService = new ApiService($client);

    }

    /**
     * Выполнить запрос на создание заявки
     *
     * @param OrderDto $orderDto
     * @return SendSellerSuccessResponseDto|SellerFailedResponseDto
     */
    public function add(OrderDto $orderDto): SendSellerSuccessResponseDto|SellerFailedResponseDto
    {
        $dto = new SendSellerRequestDto([
            'action' => config('services.sellers.list.soc_rocket.methods.add'),
            'key' => $this->key,
            'service' => $orderDto->service->api_service_id,
            'link' => $orderDto->link,
            'quantity' => $orderDto->quantity,
        ]);

        $apiRequestData = new ApiRequestData(
            '',
            'get',
            (array)$dto
        );

        try {
            $response = $this->apiService->getRequest($apiRequestData);
            Log::info('[Response Seller]', [$response]);
            if (!empty($response) && !empty($response[0])) {
                $order = $response[0];
                if (!empty($order['error'])) {
                    $responseDto = new SellerFailedResponseDto($order['error']);
                } else {
                    $responseDto = new SendSellerSuccessResponseDto($response);
                }
            } else {
                if (!empty($response['error'])) {
                    $responseDto = new SellerFailedResponseDto($response['error']);
                } else {
                    $responseDto = new SendSellerSuccessResponseDto($response);
                }
            }
        } catch (\Throwable $exception) {

            $responseDto = new SellerFailedResponseDto($exception->getMessage());
        }

        return $responseDto;
    }

    /**
     * Выполнить запрос на получение заказа
     *
     * @param OrderDto $orderDto
     * @return OrderStatusSellerSuccessResponseDto|SellerFailedResponseDto
     */
    public function order(OrderDto $orderDto): OrderStatusSellerSuccessResponseDto|SellerFailedResponseDto
    {
        $dto = new OrderStatusSellerRequestDto([
            'action' => config('services.sellers.list.soc_rocket.methods.order'),
            'order' => $orderDto->apiOrderId,
            'key' => $this->key
        ]);

        $apiRequestData = new ApiRequestData(
            '',
            'get',
            (array)$dto
        );
        try {
            $response = $this->apiService->getRequest($apiRequestData);
            $response['id'] = $orderDto->id;
            $response['order_id'] = $orderDto->apiOrderId;
            $responseDto = new OrderStatusSellerSuccessResponseDto($response);
        } catch (\Throwable $exception) {
            $responseDto = new SellerFailedResponseDto($exception->getMessage());
        }

        return $responseDto;
    }

    /**
     * Запрос на получение списка заказов
     *
     * @param Collection|array $orders
     * @return Collection
     */
    public function orders(Collection|array $orders): Collection
    {
        $ordersApiId = [];
        $ordersId = [];

        foreach($orders as $orderDto) {
            /**
             * @var OrderDto $orderDto
             */
            $ordersApiId[] = $orderDto->apiOrderId;
            $ordersId[$orderDto->apiOrderId] = $orderDto->id;
        }

        $dto = new OrdersStatusSellerRequestDto([
            'action' => config('services.sellers.list.soc_rocket.methods.orders'),
            'orders' => implode(',', $ordersApiId),
            'key' => $this->key
        ]);

        $apiRequestData = new ApiRequestData(
            '',
            'get',
            (array)$dto
        );

        $responseDtoCollection = Collection::empty();

        try {
            $response = $this->apiService->getRequest($apiRequestData);

            foreach ($response as $id => $item) {
                if (!empty($item["error"])) {
                    $responseDto = new SellerFailedResponseDto($item['error']);
                } else {
                    $item['id'] = $ordersId[$id];
                    $item['order_id'] = $id;

                    $responseDto = new OrderStatusSellerSuccessResponseDto($item);
                }
                $responseDtoCollection->push($responseDto);
            }

        } catch (\Throwable $exception) {
            $responseDto = new SellerFailedResponseDto($exception->getMessage());
            $responseDtoCollection->push($responseDto);
        }

        return $responseDtoCollection;
    }

    public function balance(): BalanceSellerSuccessResponseDto|SellerFailedResponseDto
    {
        $dto = new BalanceSellerRequestDto([
            'action' => config('services.sellers.list.soc_rocket.methods.balance'),
            'key' => $this->key
        ]);

        $apiRequestData = new ApiRequestData(
            '',
            'get',
            (array)$dto
        );

        try {
            $response = $this->apiService->getRequest($apiRequestData);

            $responseDto = new BalanceSellerSuccessResponseDto($response);
        } catch (\Throwable $exception) {
            $responseDto = new SellerFailedResponseDto($exception->getMessage());
        }

        return $responseDto;
    }


    public function services(): Collection
    {
        $dto = new ServicesSellerRequestDto([
            'action' => config('services.sellers.list.soc_rocket.methods.services'),
            'key' => $this->key
        ]);

        $apiRequestData = new ApiRequestData(
            '',
            'get',
            (array)$dto
        );

        $responseDtoCollection = Collection::empty();

        try {
            $response = $this->apiService->getRequest($apiRequestData);
            foreach ($response as $id => $item) {
                if (!empty($item["error"])) {
                    $responseDto = new SellerFailedResponseDto($item['error']);
                } else {
                    $item['cancel'] = $item['canceling_is_available'];
                    $responseDto = new ServicesSellerSuccessResponseDto($item);
                }
                $responseDtoCollection->push($responseDto);
            }
        } catch (\Throwable $exception) {
            $responseDto = new SellerFailedResponseDto($exception->getMessage());
            $responseDtoCollection->push($responseDto);
        }

        return $responseDtoCollection;
    }
}
