<?php

namespace App\Contracts;

use App\Dto\Order\OrderDto;
use Illuminate\Database\Eloquent\Collection;

interface Seller
{
    public function services(): ?Collection;
    public function add(OrderDto $orderDto);
    public function order(OrderDto $orderDto);
    public function orders(Collection|array $orders);
    public function balance();
}
