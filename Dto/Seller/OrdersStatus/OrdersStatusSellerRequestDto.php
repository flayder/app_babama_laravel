<?php

namespace App\Dto\Seller\OrdersStatus;

class OrdersStatusSellerRequestDto
{
    public string $key;
    public string $action;
    public string $orders;

    public function __construct(array $data)
    {
        $this->key = $data['key'];
        $this->action = $data['action'];
        $this->orders = $data['orders'];
    }
}
