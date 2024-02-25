<?php

namespace App\Services;

use App\Contracts\Order as OrderContract;
use App\Dto\Order\OrderCreateDto;
use App\Dto\Order\OrderDto;
use App\Dto\Seller\SellerFailedResponseDto;
use App\Dto\Seller\Send\SendSellerSuccessResponseDto;
use App\Enums\OrderStatusEnum;
use App\Enums\ServiceParameterGenderEnum;
use App\Models\Order;
use App\Models\Service;
use App\Repositories\TransactionRepository;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Database\Eloquent\Collection;
use \App\Services\SellerService;

final class OrderService extends BasicOrderService implements OrderContract
{

    public static function build(Order|OrderDto|null $order = null): OrderService
    {
        if ($order === null) {
            $order = new Order();
        }

        if ($order instanceof OrderDto) {
            $order = (new OrderRepository())->getById($order->id);
        }

        $service = new self();
        $service->order = $order;

        return $service;
    }

    public function store(OrderCreateDto $createDto): OrderDto
    {
        $this->orderCreateDto = $createDto;
        $this->promocodeService = new PromocodeService($createDto->promocode);

        if ($this->promocodeService->checkByUser($createDto->user)) {
            $this->promocodeService = new PromocodeService();
        }

        if (!empty($this->promocodeService->get())) {
            $this->promocodeService->use($createDto->user);
        }


        $service = $this->getService();

        if (!$this->getService()) {
            throw new \Error('Service не найден');
        }

        $this->prepareGender();

        if (!empty($createDto->country)) {
            $this->processOrderServiceParameters();
        }

        if (!$this->checkQuantityRules()) {
            throw new \Error("Количество должно быть минимум {$service->min_amount} и максимум {$service->max_amount}");
        }

        $this->createPrice();

        $discount = $this->promocodeService->getDiscount($this->getPrice());
        $this->setDiscount($discount);

        $this->calculate();

        $this->makeOrder();
        $this->orderCreate();

        return new OrderDto($this->order);
    }

    public function show(): OrderDto
    {
        return new OrderDto($this->order);
    }

    public function changeStatus(OrderStatusEnum $status): OrderDto
    {
        $this->order->status = $status->value;
        $this->order->saveOrFail();

        return new OrderDto($this->order);
    }

    public function pay(): OrderDto
    {
        $order = $this->order;
        $order->status = OrderStatusEnum::PROCESSING->value;
        $order->save();

        return new OrderDto($order);
    }

    public function copy(): self
    {
        $order = $this->order->replicate();
        $order->status = OrderStatusEnum::UNPAID->value;
        $order->remains = null;
        $order->save();

        return self::build($order);
    }

    public function getOrdersByUser(User $user): Collection
    {
        return $this->orderRepository->getByUser($user);
    }

    public function get(): Collection
    {
        $orders = $this->orderRepository->getAll();

        return $orders->map(fn($item) => new OrderDto($item));
    }

    public function send(SellerService $sellerService): SellerFailedResponseDto|SendSellerSuccessResponseDto
    {
        $orderDto = new OrderDto($this->order);
        $orderServiceParameter = $orderDto->serviceParameter;
        $orderServiceParameterGender = !empty($orderDto->serviceParameterGender) ? $orderDto->serviceParameterGender->value : ServiceParameterGenderEnum::ANY->value;
        if (!empty($orderServiceParameter)) {
            $serviceParameterData = $orderServiceParameter->{$orderServiceParameterGender};
            if (!empty($serviceParameterData)) {
                $serviceID = $serviceParameterData['api_service_id'];
                $service = Service::where('api_service_id', $serviceID)->first();
                if (!empty($service)) {
                    $orderDto->service = $service;
                }
            }
        }

        $sendResponse = $sellerService->send($orderDto);
        $message = '';

        if ($sendResponse instanceof SellerFailedResponseDto) {
            $message = $sendResponse->error;
        } else if ($sendResponse instanceof SendSellerSuccessResponseDto) {
            $message = 'Order: #' . $sendResponse->order;
            $this->order->api_order_id = $sendResponse->order;
        }

        $this->order->status_description = $message;
        $this->order->save();

        return $sendResponse;
    }

    public function changeStatusDescription(string $statusDescription)
    {
        $this->order->status_description = $statusDescription;
        $this->order->save();
    }

    public function getOrderPrice(): float
    {
        return $this->order->price;
    }
}
