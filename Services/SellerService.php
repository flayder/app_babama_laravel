<?php

namespace App\Services;

use App\Contracts\Seller as SellerInterface;
use App\Dto\Order\OrderDto;
use Illuminate\Database\Eloquent\Collection;

class SellerService
{
    protected SellerInterface $seller;
    public function __construct(SellerInterface $seller)
    {
        $this->seller = $seller;
    }

    public function getServices(): Collection
    {
        return $this->seller->services();
    }

    public function send(OrderDto $orderDto)
    {
        return $this->seller->add($orderDto);
    }

    public function getOrderStatus(OrderDto $orderDto)
    {
        return $this->seller->order($orderDto);
    }

    public function getOrdersStatus(Collection|array $orders)
    {
        return $this->seller->orders($orders);
    }

    public function getBalance()
    {
        return $this->seller->balance();
    }
}
