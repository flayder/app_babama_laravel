<?php

namespace App\Dto\Order;

use App\Dto\Dto;
use App\Enums\OrderStatusEnum;
use App\Enums\ServiceParameterGenderEnum;
use App\Models\Category;
use App\Models\Order;
use App\Models\Promocode;
use App\Models\Service;
use App\Models\ServiceParameter;
use App\Models\User;
use Carbon\Carbon;

class OrderDto
{
    public int $id;
    public User $user;
    public Category $category;
    public ?Service $service;
    public ?int $apiOrderId;
    public string $link;
    public int $quantity;
    public float $price;
    public ?OrderStatusEnum $status;
    public ?string $statusDescription;
    public ?string $reason;
    public ?int $agree;
    public int $startCounter;
    public ?int $remains;
    public ?int $runs;
    public ?int $interval;
    public ?int $dripFeed;
    public ?Carbon $addedOn;
    public ?Carbon $createdAt;
    public ?ServiceParameter $serviceParameter;
    public ?ServiceParameterGenderEnum $serviceParameterGender;
    public ?Promocode $promocode;
    public ?bool $newUser;



    public function __construct(Order $model)
    {
        $this->id = $model->id;
        $this->user = $model->user;
        $this->category = $model->category;
        $this->service = $model->service;
        $this->apiOrderId = $model->api_order_id;
        $this->link = $model->link;
        $this->quantity = (int)$model->quantity;
        $this->price = (float)$model->price;
        $this->status = OrderStatusEnum::from($model->status);
        $this->statusDescription = (string)$model->status_description;
        $this->reason = $model->reason;
        $this->agree = (int)$model->agree;
        $this->startCounter = (int)$model->start_counter;
        $this->remains = (int)$model->remains;
        $this->runs = $model->runs;
        $this->interval = $model->interval;
        $this->dripFeed = $model->dripFeed;
        $this->addedOn = new Carbon($model->added_on);
        $this->createdAt = new Carbon($model->created_at);
        $this->serviceParameter = $model->serviceParameter;
        $this->serviceParameterGender = ServiceParameterGenderEnum::tryFrom($model->service_parameter_gender);
        $this->promocode = $model->promocode;
        $this->newUser = $model->new_user;
    }
}
