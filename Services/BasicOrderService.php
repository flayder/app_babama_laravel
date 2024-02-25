<?php

namespace App\Services;

use App\Dto\Order\OrderCreateDto;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Service;
use App\Repositories\OrderRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Support\Facades\Log;

abstract class BasicOrderService extends Singleton
{
    protected Order $order;
    protected Service $service;

    protected OrderRepository $orderRepository;
    protected ServiceRepository $serviceRepository;

    protected OrderCreateDto $orderCreateDto;

    protected PromocodeService $promocodeService;

    private float $price = 0.0;
    private float $discount = 0.0;
    private float $totalPrice = 0.0;

    protected function __construct()
    {
        $this->serviceRepository = app(ServiceRepository::class);
        $this->orderRepository = app(OrderRepository::class);

        parent::__construct();
    }

    protected function prepareGender(): void
    {
        $gender = $this->orderCreateDto->gender;

        if (empty($gender) || $gender === 'all') {
            $this->orderCreateDto->gender = 'any';
        }
    }

    public function getService(): ?Service
    {
        if (!empty($this->service)) {
            return $this->service;
        }
        if (!empty($this->orderCreateDto)) {
            $this->service = $this->serviceRepository->getUserRate($this->orderCreateDto->service);

            return $this->getService();
        }
        if (!empty($this->order) && !empty($this->order->service)) {
            $this->service = $this->order->service;

            return $this->getService();
        }

        return null;

    }

    protected function processOrderServiceParameters(): void
    {
        $service = $this->getService();
        $createDto = $this->orderCreateDto;
        $count = $createDto->quantity;

        if (empty($createDto->country)) {
            return;
        }

        $parameter = $service->parameters()->where('country_id', $createDto->country)->first();

        if (!empty($parameter) && !empty($parameter->{$createDto->gender})) {

            if (empty($parameter->{$createDto->gender}['service_price_diff'])) {
                return;
            }

            $priceDiff = $parameter->{$createDto->gender}['service_price_diff'];

            $service->api_service_id = $parameter->{$createDto->gender}['api_service_id']; // TODO: Проверить
            $service->price += $priceDiff;
            $this->order->service_parameter_id = $parameter->id;
            $this->order->service_parameter_gender = $createDto->gender;
        }
    }

    protected function checkQuantityRules(): bool
    {
        $service = $this->getService();
        $dto = $this->orderCreateDto;
        if (!($service->min_amount <= $dto->quantity && $service->max_amount >= $dto->quantity)) {
            return false;
        }

        return true;
    }

    protected function createPrice(): void
    {
        $service = $this->getService();

//        $userRate = $service->user_rate ?? $service->price;
        $userRate = $service->price;

        $this->price = round(($this->orderCreateDto->quantity * $userRate) / 1000, 2);
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setTotalPrice(float $price): void
    {
        $this->totalPrice = $price;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    protected function calculate()
    {
        $totalPrice = $this->price - $this->discount;

        if ($totalPrice < 0) {
            $totalPrice = 0.00;
        }

        $this->setTotalPrice($totalPrice);
    }

    protected function makeOrder()
    {
        $order = $this->order;
        $dto = $this->orderCreateDto;
        $order->user_id = $dto->user->id;
        $order->category_id = $dto->category;
        $order->service_id = $dto->service;
        $order->link = $dto->link;
        $order->quantity = $dto->quantity;
        $order->status = OrderStatusEnum::UNPAID->value;
        $order->price = $this->totalPrice;
        $order->runs = $dto->runs;
        $order->interval = $dto->interval;
        $order->promocode_id = $this->promocodeService->get()?->id;
        $order->new_user = $dto->newUser;
    }

    protected function orderCreate()
    {
        $this->order->saveOrFail();
    }
}
