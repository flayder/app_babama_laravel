<?php

namespace App\Dto\Seller\OrderStatus;

class OrderStatusSellerRequestDto
{
    public string $key;
    public string $action;
    public int $order;

    public function __construct(array $data)
    {
        $this->key = $data['key'];
        $this->action = $data['action'];
        $this->order = $data['order'];
    }
}
